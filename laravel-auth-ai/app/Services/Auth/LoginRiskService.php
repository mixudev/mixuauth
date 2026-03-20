<?php

namespace App\Services\Auth;

use App\Models\LoginLog;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\Security\DeviceFingerprintService;
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
        $ip               = $request->ip();
        $fingerprint      = $this->fingerprintService->generate($request);
        $failedAttempts   = $this->getRecentFailedAttempts($user->id, $ip);
        $isNewDevice      = $this->isNewDevice($user->id, $fingerprint);
        $isNewCountry     = $this->isNewCountry($user->id, $request);
        $ipRiskData       = $this->assessIpRisk($ip);
        $loginHour        = (int) now()->format('H');
        $requestSpeed     = $this->calculateRequestSpeed($ip);

        $payload = [
            // Identitas permintaan (abstraksi)
            'user_id'             => $user->id,
            'ip_risk_score'       => (float) ($ipRiskData['risk_score'] / 100), // Normalisasi ke 0.0–1.0
            'is_vpn'              => $ipRiskData['is_vpn'] ? 1 : 0,
            'is_new_device'       => $isNewDevice ? 1 : 0,
            'is_new_country'      => $isNewCountry ? 1 : 0,
            'login_hour'          => $loginHour,
            'failed_attempts'     => $failedAttempts,
            'request_speed'       => min((float) ($requestSpeed / 10), 1.0), // Normalisasi (cap pada 10 req/min)
            'device_trust_score'  => $isNewDevice ? 0.5 : 1.0,
            'device_fingerprint'  => $fingerprint,
            'timestamp'           => now()->toIso8601String(),
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
    private function isNewCountry(int $userId, Request $request): bool
    {
        $currentCountry = $this->resolveCountryCode($request->ip());

        if (empty($currentCountry)) {
            // Jika negara tidak dapat ditentukan, anggap baru untuk keamanan
            return true;
        }

        // Cek apakah pengguna pernah login dari negara ini sebelumnya
        $hasLoginFromCountry = LoginLog::query()
            ->where('user_id', $userId)
            ->where('country_code', $currentCountry)
            ->where('status', LoginLog::STATUS_SUCCESS)
            ->exists();

        return ! $hasLoginFromCountry;
    }

    /**
     * Nilai risiko IP menggunakan cache lokal.
     * Di deployment nyata, ini dapat dikombinasikan dengan layanan
     * reputasi IP seperti AbuseIPDB atau MaxMind.
     *
     * Fungsi ini mengembalikan skor abstrak (bukan IP mentah) ke AI.
     *
     * @return array{risk_score: int, is_vpn: bool}
     */
    private function assessIpRisk(string $ip): array
    {
        $cacheKey = "ip_risk:{$ip}";

        // Simpan dalam cache selama 1 jam untuk mengurangi lookup berulang
        return Cache::remember($cacheKey, now()->addHour(), function () use ($ip) {
            // Contoh logika dasar — ganti dengan integrasi API reputasi IP nyata
            $isPrivate = $this->isPrivateIp($ip);

            if ($isPrivate) {
                // IP internal dianggap aman (deployment Docker/jaringan internal)
                return ['risk_score' => 0, 'is_vpn' => false];
            }

            // Nilai default jika tidak ada integrasi eksternal
            // Pada deployment produksi, panggil MaxMind / AbuseIPDB di sini
            return ['risk_score' => 10, 'is_vpn' => false];
        });
    }

    /**
     * Hitung kecepatan permintaan (requests per menit) dari IP yang sama.
     * Digunakan sebagai sinyal aktivitas bot atau brute-force.
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
     * Pada deployment produksi, gunakan database GeoIP seperti MaxMind GeoLite2.
     */
    private function resolveCountryCode(string $ip): string
    {
        if ($this->isPrivateIp($ip)) {
            return 'INTERNAL';
        }

        // Placeholder — integrasikan dengan MaxMind atau layanan GeoIP
        return Cache::get("geoip:{$ip}", '');
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
