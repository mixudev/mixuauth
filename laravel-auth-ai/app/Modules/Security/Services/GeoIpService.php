<?php

namespace App\Modules\Security\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    /**
     * Resolusi kode negara berdasarkan alamat IP.
     * Menggunakan ip-api.com (Free Tier) dengan caching 24 jam.
     */
    public function getCountryCode(string $ip): string
    {
        // Jangan lakukan lookup untuk IP lokal/private
        if ($this->isPrivateIp($ip)) {
            return 'ID'; // Default atau penanda lokal
        }

        return Cache::remember("geoip:v2:{$ip}", now()->addHours(24), function () use ($ip) {
            try {
                // Gunakan ip-api.com (tanpa API key untuk demo/free tier)
                // Timeout singkat agar tidak menghambat login jika API lambat
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}?fields=status,message,countryCode");

                if ($response->successful() && $response->json('status') === 'success') {
                    return (string) $response->json('countryCode');
                }

                Log::channel('security')->warning('GeoIP Lookup gagal atau IP tidak ditemukan', [
                    'ip'      => $ip,
                    'status'  => $response->status(),
                    'message' => $response->json('message')
                ]);

            } catch (\Exception $e) {
                Log::channel('security')->error('Kesalahan koneksi GeoIP API', [
                    'ip'    => $ip,
                    'error' => $e->getMessage()
                ]);
            }

            return 'ID'; // Fallback aman (asumsi pasar utama Indonesia atau sesuaikan)
        });
    }

    /**
     * Periksa apakah IP merupakan alamat privat/lokal.
     */
    private function isPrivateIp(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
