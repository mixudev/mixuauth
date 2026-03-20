<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\TrustedDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * DEV ONLY
 *
 * Lists and manages trusted devices.
 * Toggle revoke/restore via a single endpoint (idempotent flip).
 *
 * Recommended indexes:
 *   trusted_devices: (id DESC), (is_revoked, id DESC)
 */
class DevTrustedDeviceController extends Controller
{
    private const PAGE_SIZE = 50;

    public function index(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');

        $rows = TrustedDevice::select([
                'trusted_devices.id',
                'trusted_devices.fingerprint_hash',
                'trusted_devices.device_label',
                'trusted_devices.ip_address',
                'trusted_devices.is_revoked',
                'trusted_devices.last_seen_at',
                'trusted_devices.trusted_until',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->join('users', 'users.id', '=', 'trusted_devices.user_id')
            ->when($cursor > 0,           fn($q) => $q->where('trusted_devices.id', '<', $cursor))
            ->when($status === 'trusted', fn($q) => $q->where('is_revoked', false))
            ->when($status === 'revoked', fn($q) => $q->where('is_revoked', true))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',                 'like', "%{$search}%")
                   ->orWhere('trusted_devices.ip_address','like', "%{$search}%")
                   ->orWhere('users.email',              'like', "%{$search}%")
            ))
            ->orderByDesc('trusted_devices.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($d) => [
            'id'            => $d->id,
            'user'          => $d->user_name,
            'email'         => $d->user_email,
            'fingerprint'   => substr($d->fingerprint_hash, 0, 16) . '…',
            'device_label'  => $d->device_label ?? '—',
            'ip_address'    => $d->ip_address,
            'is_revoked'    => $d->is_revoked,
            'last_seen'     => $d->last_seen_at?->toDateTimeString() ?? '—',
            'trusted_until' => $d->trusted_until?->toDateTimeString() ?? '—',
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function revoke(int $deviceId): JsonResponse
    {
        $device = TrustedDevice::select(['id', 'is_revoked', 'fingerprint_hash', 'user_id'])
            ->with('user:id,name')
            ->find($deviceId);

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        }

        $newState = !$device->is_revoked;
        $device->update(['is_revoked' => $newState]);

        Cache::forget("device_blocked:{$device->fingerprint_hash}");

        return response()->json([
            'success'    => true,
            'is_revoked' => $newState,
            'message'    => 'Device ' . ($newState ? 'direvoke' : 'di-restore') . " untuk user {$device->user?->name}.",
        ]);
    }
}
