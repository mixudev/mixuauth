<?php

namespace App\Modules\Security\Services;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use App\Modules\Security\Middleware\DeviceIdentifierMiddleware;

class DeviceFingerprintService
{
    public function __construct(
        private readonly GeoIpService $geoIpService
    ) {}

    /**
     * Dapatkan kode negara berdasarkan IP (ISO 3166-1 alpha-2).
     */
    public function getCountry(string $ip): string
    {
        return $this->geoIpService->getCountryCode($ip);
    }

    /**
     * Dapatkan IP asli pengunjung dengan memeriksa header proxy yang umum.
     * Jika di lingkungan local/dev, mencoba mengambil IP publik asli perangkat untuk keperluan Risk AI.
     */
    public function getRealIp(Request $request): string
    {
        $ip = (string) $request->ip();

        // Daftar IP lokal/private yang perlu di-bypass di mode development
        $localIps = ['127.0.0.1', '::1', '172.', '192.168.', '10.'];
        $isLocal = false;
        foreach ($localIps as $prefix) {
            if (str_starts_with($ip, $prefix)) {
                $isLocal = true;
                break;
            }
        }

        // Jika IP terdeteksi lokal dan bukan di production, coba ambil IP publik asli
        if ($isLocal && config('app.env') !== 'production') {
            return cache()->remember('real_public_ip_' . $ip, 3600, function () use ($ip) {
                try {
                    $client = new \GuzzleHttp\Client(['timeout' => 2]);
                    $response = $client->get('https://api.ipify.org');
                    $publicIp = (string) $response->getBody();
                    
                    if (filter_var($publicIp, FILTER_VALIDATE_IP)) {
                        return $publicIp;
                    }
                } catch (\Exception $e) {
                    // Fallback ke IP lokal jika gagal fetch
                }
                return $ip;
            });
        }

        return $ip;
    }

    /**
     * Dapatkan rincian spesifik perangkat untuk diteruskan ke AI.
     */
    public function getDetailedDevice(Request $request): array
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent() ?? '');

        $deviceType = 'Desktop';
        if ($agent->isTablet()) {
            $deviceType = 'Tablet';
        } elseif ($agent->isMobile()) {
            $deviceType = 'Mobile';
        } elseif ($agent->isRobot()) {
            $deviceType = 'Robot';
        }

        return [
            'browser'         => $agent->browser() ?: 'Unknown Browser',
            'browser_version' => $agent->version($agent->browser() ?: '') ?: 'Unknown',
            'os'              => $agent->platform() ?: 'Unknown OS',
            'os_version'      => $agent->version($agent->platform() ?: '') ?: 'Unknown',
            'device_type'     => $deviceType,
            'is_bot'          => $agent->isRobot() ? 1 : 0,
            'bot_name'        => $agent->isRobot() ? $agent->robot() : null,
        ];
    }
    /*
    |--------------------------------------------------------------------------
    | Layanan untuk menghasilkan dan memvalidasi fingerprint perangkat.
    |
    | Fingerprint dibuat dari kombinasi atribut HTTP yang tersedia tanpa
    | menyimpan data identifikasi pribadi secara langsung.
    | Nilai akhir di-hash sehingga tidak dapat dibalikkan.
    |--------------------------------------------------------------------------
    */

    /**
     * Hasilkan hash fingerprint unik untuk request saat ini.
     * Menggunakan ID unik dari cookie sebagai komponen identitas utama yang stabil.
     */
    public function generate(Request $request): string
    {
        $deviceToken = $this->getDeviceToken($request);

        if (! $deviceToken) {
            $userAgent = strtolower(trim($request->userAgent() ?? 'none'));
            return hash('sha256', "legacy|{$userAgent}");
        }

        return hash('sha256', $deviceToken);
    }

    public function getDeviceToken(Request $request): ?string
    {
        $token = $request->cookie(DeviceIdentifierMiddleware::COOKIE_NAME)
            ?: $request->cookie('device_trust_id');

        if (! is_string($token) || $token === '') {
            return null;
        }

        return $token;
    }

    /**
     * Dapatkan tanda tangan perangkat (Device Signature) untuk verifikasi integritas.
     * Digunakan untuk memastikan cookie tidak dipindahkan ke perangkat lain yang berbeda jauh.
     */
    public function getDeviceSignature(Request $request): string
    {
        $details = $this->getDetailedDevice($request);
        
        // Kita gunakan Browser Utama dan OS Utama sebagai signature.
        // Versi browser sengaja tidak dimasukkan agar update browser otomatis tidak memicu OTP.
        $browser = $details['browser'];
        $os      = $details['os'];
        $type    = $details['device_type'];

        return hash('sha256', "sig|{$browser}|{$os}|{$type}");
    }

    /**
     * Buat UUID baru untuk identitas perangkat.
     */
    public function generateNewDeviceId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Hasilkan label perangkat yang ramah pengguna dari User-Agent string.
     * Digunakan untuk ditampilkan pada halaman manajemen perangkat.
     */
    public function buildDeviceLabel(Request $request): string
    {
        $details = $this->getDetailedDevice($request);
        $browser = trim("{$details['browser']} {$details['browser_version']}");
        $os = trim("{$details['os']} {$details['os_version']}");
        
        $browser = $browser === '' || $browser === 'Unknown Browser Unknown' ? 'Browser Tidak Dikenal' : $browser;
        $os = $os === '' || $os === 'Unknown OS Unknown' ? 'OS Tidak Dikenal' : $os;

        return "{$browser} di {$os}";
    }

    /**
     * Periksa apakah fingerprint saat ini cocok dengan hash yang tersimpan.
     */
    public function matches(Request $request, string $storedHash): bool
    {
        return hash_equals($storedHash, $this->generate($request));
    }
}
