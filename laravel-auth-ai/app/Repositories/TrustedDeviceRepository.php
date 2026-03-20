<?php

namespace App\Repositories;

use App\Models\TrustedDevice;
use App\Services\Security\DeviceFingerprintService;
use Illuminate\Http\Request;

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
     * Jika fingerprint sudah ada, perbarui masa berlakunya.
     */
    public function trustDevice(int $userId, Request $request): TrustedDevice
    {
        $fingerprint  = $this->fingerprintService->generate($request);
        $deviceLabel  = $this->fingerprintService->buildDeviceLabel($request);
        $trustedDays  = config('security.session.trusted_device_days', 30);

        return TrustedDevice::updateOrCreate(
            [
                'user_id'          => $userId,
                'fingerprint_hash' => $fingerprint,
            ],
            [
                'device_label'  => $deviceLabel,
                'ip_address'    => $request->ip(),
                'last_seen_at'  => now(),
                'trusted_until' => now()->addDays($trustedDays),
                'is_revoked'    => false,
            ]
        );
    }

    /**
     * Periksa apakah fingerprint saat ini sudah terpercaya untuk pengguna tertentu.
     */
    public function isTrusted(int $userId, string $fingerprint): bool
    {
        return TrustedDevice::where('user_id', $userId)
            ->where('fingerprint_hash', $fingerprint)
            ->active()
            ->exists();
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
