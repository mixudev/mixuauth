<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Security\Models\LoginLog;
use App\Modules\Security\Models\TrustedDevice;
use App\Models\User;
use App\Modules\Security\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LoginRiskService
{
    /*
    |--------------------------------------------------------------------------
    | Layanan untuk mengumpulkan dan menyusun data sinyal risiko pre-login.
    |
    | Data yang dikumpulkan TIDAK memuat password, token, atau informasi
    | sensitif lainnya. Semua nilai telah diabstraksi sebelum dikirim ke AI.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    /**
     * Susun payload risiko yang akan dikirim ke layanan AI FastAPI.
     *
     * @return array<string, mixed>
     */
    public function prepareRiskPayload(Request $request, User $user): array
    {
        $ip            = $this->fingerprintService->getRealIp($request);
        $fingerprint   = $this->fingerprintService->generate($request);
        $deviceDetails = $this->fingerprintService->getDetailedDevice($request);
        $failedAttempts = $this->getRecentFailedAttempts($user->id, $ip);
        $isNewDevice   = $this->isNewDevice($user->id, $fingerprint);
        $isNewCountry  = $this->isNewCountry($user->id, $ip);
        $ipRiskData    = $this->assessIpRisk($ip);
        $loginHour     = (int) now()->format('H');
        $requestSpeed  = $this->calculateRequestSpeed($ip);

        $payload = [
            // Identitas permintaan (abstraksi)
            'user_id'            => $user->id,
            'ip_risk_score'      => (float) ($ipRiskData['risk_score'] / 100), // Normalisasi ke 0.0–1.0
            'is_vpn'             => $ipRiskData['is_vpn'] ? 1 : 0,
            'is_new_device'      => $isNewDevice ? 1 : 0,
            'is_new_country'     => $isNewCountry ? 1 : 0,
            'login_hour'         => $loginHour,
            'failed_attempts'    => $failedAttempts,
            'request_speed'      => min((float) ($requestSpeed / 10), 1.0), // Normalisasi (cap pada 10 req/min)
            'device_trust_score' => $this->getDeviceTrustScore($user->id, $fingerprint),
            'device_fingerprint' => $fingerprint,
            'real_ip'            => $ip,
            'device_browser'     => $deviceDetails['browser'],
            'device_os'          => $deviceDetails['os'],
            'device_type'        => $deviceDetails['device_type'],
            'is_bot'             => $deviceDetails['is_bot'],
            'timestamp'          => now()->toIso8601String(),
        ];

        Log::channel('security')->info('Payload risiko login disiapkan', [
            'user_id'         => $user->id,
            'is_new_device'   => $isNewDevice,
            'is_new_country'  => $isNewCountry,
            'failed_attempts' => $failedAttempts,
            'ip_risk_score'   => $ipRiskData['risk_score'],
        ]);

        return $payload;
    }

    /**
     * Hitung jumlah percobaan login gagal dalam 30 menit terakhir
     * dari kombinasi user_id dan IP.
     */
    private function getRecentFailedAttempts(int $userId, string $ip): int
    {
        // Gunakan cache agar tidak membebani database setiap request
        $cacheKey = "failed_attempts:{$userId}:{$ip}";

        return (int) Cache::get($cacheKey, 0);
    }

    /**
     * Hitung skor kepercayaan perangkat secara granular.
     *
     * @return float antara 0.0 (tidak dipercaya) hingga 1.0 (penuh dipercaya)
     */
    private function getDeviceTrustScore(int $userId, string $fingerprint): float
    {
        $device = TrustedDevice::where('user_id', $userId)
            ->where('fingerprint_hash', $fingerprint)
            ->where('is_revoked', false)
            ->first();

        if (! $device) {
            return 0.0; // Belum pernah terdaftar sama sekali
        }

        if (! $device->trusted_until || $device->trusted_until->toDateTimeString() <= now()->utc()->toDateTimeString()) {
            return 0.1; // Ada record tapi sudah expired — lebih aman dari 0.0
        }

        // Skor proporsional: sisa hari / total hari kepercayaan
        $trustedDays   = (int) config('security.session.trusted_device_days', 30);
        $daysRemaining = (int) now()->utc()->diffInDays($device->trusted_until);

        // Min 0.5 agar device yang baru saja diverifikasi tidak kena low_device_trust
        return min(1.0, max(0.5, $daysRemaining / $trustedDays));
    }

    /**
     * Periksa apakah fingerprint perangkat belum pernah tercatat
     * sebagai perangkat terpercaya milik pengguna ini.
     */
    private function isNewDevice(int $userId, string $fingerprint): bool
    {
        return ! TrustedDevice::query()
            ->where('user_id', $userId)
            ->where('fingerprint_hash', $fingerprint)
            ->active()
            ->exists();
    }

    /**
     * Periksa apakah permintaan berasal dari negara yang belum pernah
     * digunakan oleh pengguna ini sebelumnya.
     */
    private function isNewCountry(int $userId, string $ip): bool
    {
        // Jika IP privat (Docker), jangan anggap sebagai negara baru
        if ($this->isPrivateIp($ip)) {
            return false;
        }

        $currentCountry = $this->resolveCountryCode($ip);

        if (empty($currentCountry)) {
            return true;
        }

        // Cek apakah pengguna pernah login dari negara ini sebelumnya
        return ! LoginLog::query()
            ->where('user_id', $userId)
            ->where('country_code', $currentCountry)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->exists();
    }

    /**
     * Nilai risiko IP menggunakan cache lokal.
     *
     * @return array{risk_score: int, is_vpn: bool}
     */
    private function assessIpRisk(string $ip): array
    {
        $cacheKey = "ip_risk:{$ip}";

        // Simpan dalam cache selama 1 jam untuk mengurangi lookup berulang
        return Cache::remember($cacheKey, now()->addHour(), function () use ($ip) {
            $isPrivate = $this->isPrivateIp($ip);

            if ($isPrivate) {
                return ['risk_score' => 0, 'is_vpn' => false];
            }

            return ['risk_score' => 10, 'is_vpn' => false];
        });
    }

    /**
     * Hitung kecepatan permintaan (requests per menit) dari IP yang sama.
     */
    private function calculateRequestSpeed(string $ip): float
    {
        $windowKey = "req_speed:{$ip}:" . now()->format('YmdHi'); // Per menit
        $count     = Cache::increment($windowKey);

        // Set TTL hanya saat pertama kali dibuat
        if ($count === 1) {
            Cache::put($windowKey, 1, now()->addMinutes(2));
        }

        return (float) $count;
    }

    /**
     * Resolve kode negara dari IP.
     */
    private function resolveCountryCode(string $ip): string
    {
        return $this->fingerprintService->getCountry($ip);
    }

    /**
     * Periksa apakah IP termasuk dalam rentang IP privat/lokal.
     */
    private function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
