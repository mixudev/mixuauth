<?php

namespace App\Services\Security;

use App\DTOs\RiskAssessmentResult;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class RiskFallbackService
{
    /*
    |--------------------------------------------------------------------------
    | Layanan penilaian risiko berbasis aturan (rule-based).
    |
    | Diaktifkan HANYA saat layanan AI FastAPI tidak dapat dijangkau.
    | Sistem ini konservatif: lebih memilih OTP daripada BLOCK otomatis
    | untuk menghindari false positive yang merugikan pengguna sah.
    |
    | PENTING: Sistem fallback TIDAK PERNAH mengizinkan login dengan
    | risiko tinggi secara otomatis tanpa validasi tambahan.
    |--------------------------------------------------------------------------
    */

    /**
     * Hitung skor risiko menggunakan aturan statis berdasarkan payload yang ada.
     *
     * @param  array<string, mixed>  $riskPayload
     */
    public function assess(array $riskPayload): RiskAssessmentResult
    {
        $score       = 0;
        $reasonFlags = [];
        $weights     = config('security.fallback_scoring');

        // -- Sinyal: Perangkat baru
        if ($riskPayload['is_new_device'] ?? false) {
            $score       += $weights['new_device_weight'];
            $reasonFlags[] = 'new_device';
        }

        // -- Sinyal: Negara baru
        if ($riskPayload['is_new_country'] ?? false) {
            $score       += $weights['new_country_weight'];
            $reasonFlags[] = 'new_country';
        }

        // -- Sinyal: VPN terdeteksi
        if ($riskPayload['is_vpn'] ?? false) {
            $score       += $weights['vpn_weight'];
            $reasonFlags[] = 'vpn_detected';
        }

        // -- Sinyal: Percobaan gagal berulang
        $failedAttempts = (int) ($riskPayload['failed_attempts'] ?? 0);
        if ($failedAttempts > 0) {
            $addedScore  = min($failedAttempts * $weights['failed_attempts_multiplier'], 30);
            $score       += $addedScore;
            $reasonFlags[] = "failed_attempts:{$failedAttempts}";
        }

        // -- Sinyal: Login di luar jam kerja (sebelum 06:00 atau setelah 22:00)
        $loginHour = (int) ($riskPayload['login_hour'] ?? 12);
        if ($loginHour < 6 || $loginHour >= 22) {
            $score       += $weights['odd_hour_weight'];
            $reasonFlags[] = 'off_hours_login';
        }

        // -- Sinyal: Skor risiko IP
        $ipRiskScore = (int) ($riskPayload['ip_risk_score'] ?? 0);
        if ($ipRiskScore > 0) {
            $score       += (int) ($ipRiskScore * $weights['ip_risk_multiplier']);
            if ($ipRiskScore > 50) {
                $reasonFlags[] = 'high_risk_ip';
            }
        }

        // Batasi skor maksimum pada 100
        $score = min($score, 100);

        $decision = $this->makeDecision($score);

        $reasonFlags[] = 'fallback_mode'; // Tandai bahwa ini adalah penilaian fallback

        Log::channel('security')->warning('Mode fallback rule-based aktif', [
            'calculated_score' => $score,
            'decision'         => $decision,
            'reason_flags'     => $reasonFlags,
        ]);

        return new RiskAssessmentResult(
            riskScore:   $score,
            decision:    $decision,
            reasonFlags: $reasonFlags,
            rawResponse: ['source' => 'rule_based_fallback', 'score' => $score],
            isFallback:  true,
        );
    }

    /**
     * Terapkan threshold keputusan berdasarkan skor yang telah dihitung.
     * Mode fallback lebih ketat: threshold ALLOW diturunkan untuk keamanan.
     */
    private function makeDecision(int $score): string
    {
        $thresholds = config('security.risk_thresholds');

        $allowThreshold = (int) ($thresholds['allow'] * 0.8); // 30 * 0.8 = 24

        // Jika OTP tidak diaktifkan, ubah threshold OTP menjadi threshold BLOCK
        $otpThreshold = config('security.otp.enabled') 
            ? $thresholds['otp']
            : $thresholds['allow']; // Jika OTP mati, apapun di atas allowThreshold langsung BLOCK

        return match (true) {
            $score < $allowThreshold       => LoginLog::DECISION_ALLOW,
            $score < $otpThreshold         => LoginLog::DECISION_OTP,
            default                        => LoginLog::DECISION_BLOCK,
        };
    }
}
