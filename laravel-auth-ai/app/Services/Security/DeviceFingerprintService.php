<?php

namespace App\Services\Security;

use Illuminate\Http\Request;

class DeviceFingerprintService
{
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
     * Hash bersifat deterministik: input yang sama menghasilkan hash yang sama.
     */
    public function generate(Request $request): string
    {
        // Kumpulkan sinyal-sinyal yang stabil dan sulit dipalsukan
        $signals = [
            'user_agent'      => $request->userAgent() ?? '',
            'accept_language' => $request->header('Accept-Language', ''),
            'accept_encoding' => $request->header('Accept-Encoding', ''),
            'accept'          => $request->header('Accept', ''),
            // Catatan: IP sengaja tidak dimasukkan agar fingerprint tetap valid
            // meskipun pengguna berganti jaringan (misalnya WiFi ke data seluler)
        ];

        $raw = implode('|', $signals);

        // Gunakan SHA-256 sebagai hash non-kriptografis yang cepat
        return hash('sha256', $raw);
    }

    /**
     * Hasilkan label perangkat yang ramah pengguna dari User-Agent string.
     * Digunakan untuk ditampilkan pada halaman manajemen perangkat.
     */
    public function buildDeviceLabel(Request $request): string
    {
        $userAgent = $request->userAgent() ?? '';

        // Deteksi browser
        $browser = match (true) {
            str_contains($userAgent, 'Edg/')     => 'Edge',
            str_contains($userAgent, 'Chrome/')  => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/')  => 'Safari',
            default                              => 'Browser Tidak Dikenal',
        };

        // Deteksi sistem operasi
        $os = match (true) {
            str_contains($userAgent, 'Windows')  => 'Windows',
            str_contains($userAgent, 'Macintosh') => 'Mac',
            str_contains($userAgent, 'Linux')    => 'Linux',
            str_contains($userAgent, 'Android')  => 'Android',
            str_contains($userAgent, 'iPhone')   => 'iPhone',
            default                              => 'Perangkat Tidak Dikenal',
        };

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
