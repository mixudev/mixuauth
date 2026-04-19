<?php

namespace App\Modules\Security\Repositories;

use App\Modules\Security\Models\TrustedDevice;
use App\Modules\Security\Services\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrustedDeviceRepository
{
    /*
    |--------------------------------------------------------------------------
    | Repository untuk mengelola data perangkat terpercaya.
    | Memisahkan logika akses database dari service layer.
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private readonly DeviceFingerprintService $fingerprintService
    ) {}

    /**
     * Daftarkan perangkat baru sebagai perangkat terpercaya milik pengguna.
     * Menggunakan UUID dari cookie sebagai identitas utama.
     */
    public function trustDevice(int $userId, Request $request): ?TrustedDevice
    {
        $fingerprintHash = $this->fingerprintService->generate($request);

        if ($fingerprintHash === '') {
            return null;
        }

        $deviceLabel  = $this->fingerprintService->buildDeviceLabel($request);
        $trustedDays  = config('security.session.trusted_device_days', 30);
        $ip           = $this->fingerprintService->getRealIp($request);
        $country      = $this->fingerprintService->getCountry($ip);
        $signature    = $this->fingerprintService->getDeviceSignature($request);

        // Simpan UUID cookie ke kolom fingerprint_hash sebagai ID utama yang stabil.
        // Simpan signature untuk verifikasi UA-Binding.
        $device = TrustedDevice::updateOrCreate(
            [
                'user_id'          => $userId,
                'fingerprint_hash' => $fingerprintHash,
            ],
            [
                'device_signature' => $signature,
                'device_label'     => $deviceLabel,
                'ip_address'       => $ip,
                'country_code'     => $country,
                'last_seen_at'     => now()->utc(),
                'trusted_until'    => now()->utc()->addDays($trustedDays),
                'is_revoked'       => false,
            ]
        );

        Log::channel('security')->info('Device dipercaya/diperbarui dengan UA-Binding', [
            'user_id'       => $userId,
            'fingerprint'   => substr($fingerprintHash, 0, 12) . '...',
            'signature'     => substr($signature, 0, 8) . '...',
            'ip'            => $ip,
            'trusted_until' => $device->trusted_until?->toDateTimeString(),
        ]);

        return $device;
    }

    /**
     * Periksa apakah fingerprint saat ini sudah terpercaya untuk pengguna tertentu.
     */
    public function isTrusted(int $userId, string $fingerprint): bool
    {
        $device = TrustedDevice::where('user_id', $userId)
            ->where('fingerprint_hash', $fingerprint)
            ->first();

        $nowUtc = now()->utc();
        $nowStr = $nowUtc->toDateTimeString();

        if (! $device || $device->is_revoked) {
            return false;
        }

        // Cek apakah masa berlaku kepercayaan sudah habis
        if (! $device->trusted_until || $device->trusted_until->toDateTimeString() <= $nowStr) {
            return false;
        }

        $request = request();
        $currentIp = $this->fingerprintService->getRealIp($request);

        // ── KEAMANAN 1: UA-Binding (Mencegah Cookie Stealing antar browser/perangkat) ──
        $currentSignature = $this->fingerprintService->getDeviceSignature($request);
        if ($device->device_signature && !hash_equals($device->device_signature, $currentSignature)) {
            Log::channel('security')->warning('Trusted Device ID Cocok, TAPI SIGNATURE PERANGKAT BERBEDA (Potensi Manipulasi)', [
                'user_id'   => $userId,
                'device_id' => substr($fingerprint, 0, 8) . '...',
                'ip'        => $currentIp
            ]);
            return false;
        }

        // ── KEAMANAN 2: Geo-Binding (Mencegah Cookie Stealing antar negara) ──
        $currentCountry = $this->fingerprintService->getCountry($currentIp);

        // Jika negara terekam dan negara saat ini terdeteksi berbeda, tolak bypass.
        if ($device->country_code && $currentCountry !== 'XX' && $device->country_code !== $currentCountry) {
             Log::channel('security')->warning('Trusted Device ID Cocok, TAPI LOKASI BERBEDA (Potensi Pencurian Cookie)', [
                'user_id'          => $userId,
                'stored_country'   => $device->country_code,
                'current_country'  => $currentCountry,
                'ip'               => $currentIp
            ]);
            return false;
        }

        return true;
    }

    /**
     * Ambil semua perangkat aktif milik pengguna.
     */
    public function getActiveDevices(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return TrustedDevice::where('user_id', $userId)
            ->active()
            ->orderByDesc('last_seen_at')
            ->get();
    }

    /**
     * Cabut semua perangkat terpercaya milik pengguna (paksa login ulang semua perangkat).
     */
    public function revokeAll(int $userId): int
    {
        return TrustedDevice::where('user_id', $userId)
            ->update(['is_revoked' => true]);
    }
}
