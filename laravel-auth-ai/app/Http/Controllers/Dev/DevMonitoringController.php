<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use App\Models\IpWhitelist;
use App\Models\LoginLog;
use App\Models\OtpVerification;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\BlockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DEV ONLY — Hapus atau proteksi controller ini sebelum production!
 */
class DevMonitoringController extends Controller
{
    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    public function dashboard(): \Illuminate\View\View
    {
        return view('dev.monitoring');
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'users'            => User::count(),
            'active_users'     => User::where('is_active', true)->count(),
            'total_logs'       => LoginLog::count(),
            'blocked_logs'     => LoginLog::where('decision', 'BLOCK')->count(),
            'active_otps'      => OtpVerification::where('expires_at', '>', now())->whereNull('verified_at')->count(),
            'trusted_devices'  => TrustedDevice::where('is_revoked', false)->count(),
            'ip_blacklisted'   => IpBlacklist::active()->count(),
            'ip_whitelisted'   => IpWhitelist::count(),
            'users_blocked'    => UserBlock::active()->count(),
        ]);
    }

    public function otps(): JsonResponse
    {
        $otps = OtpVerification::with('user:id,name,email')
            ->latest()->limit(50)->get()
            ->map(fn($otp) => [
                'id'          => $otp->id,
                'user'        => $otp->user?->name ?? '—',
                'email'       => $otp->user?->email ?? '—',
                'otp_code'    => $otp->token,
                'status'      => $otp->verified_at ? 'verified' : ($otp->expires_at < now() ? 'expired' : 'active'),
                'attempts'    => $otp->attempts ?? 0,
                'expires_at'  => $otp->expires_at?->toDateTimeString(),
                'verified_at' => $otp->verified_at?->toDateTimeString(),
                'created_at'  => $otp->created_at->toDateTimeString(),
            ]);

        return response()->json($otps);
    }

    public function loginLogs(): JsonResponse
    {
        $logs = LoginLog::with('user:id,name,email')
            ->orderBy('occurred_at', 'desc')->limit(100)->get()
            ->map(fn($log) => [
                'id'           => $log->id,
                'user'         => $log->user?->name ?? 'Unknown',
                'email'        => $log->user?->email ?? $log->email_attempted ?? '—',
                'ip_address'   => $log->ip_address,
                'country_code' => $log->country_code,
                'user_agent'   => $log->user_agent,
                'device_fp'    => $log->device_fingerprint ? substr($log->device_fingerprint, 0, 16) . '...' : '—',
                'status'       => $log->status,
                'decision'     => $log->decision ?? '—',
                'risk_score'   => $log->risk_score,
                'reason_flags' => $log->reason_flags,
                'is_fallback'  => $log->status === 'fallback',
                'occurred_at'  => $log->occurred_at->toDateTimeString(),
            ]);

        return response()->json($logs);
    }

    public function trustedDevices(): JsonResponse
    {
        $devices = TrustedDevice::with('user:id,name,email')
            ->latest()->limit(100)->get()
            ->map(fn($device) => [
                'id'            => $device->id,
                'user'          => $device->user?->name ?? '—',
                'email'         => $device->user?->email ?? '—',
                'fingerprint'   => substr($device->fingerprint_hash, 0, 16) . '...',
                'fingerprint_full' => $device->fingerprint_hash,
                'user_id'       => $device->user_id,
                'device_label'  => $device->device_label ?? '—',
                'ip_address'    => $device->ip_address,
                'country_code'  => $device->country_code,
                'is_revoked'    => $device->is_revoked,
                'last_seen'     => $device->last_seen_at?->toDateTimeString() ?? '—',
                'trusted_until' => $device->trusted_until?->toDateTimeString() ?? '—',
                'created_at'    => $device->created_at->toDateTimeString(),
            ]);

        return response()->json($devices);
    }

    public function users(): JsonResponse
    {
        $blockedUserIds = UserBlock::active()->pluck('user_id')->toArray();

        $users = User::withCount(['loginLogs', 'trustedDevices'])
            ->latest()->get()
            ->map(fn($user) => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'is_active'     => $user->is_active,
                'verified'      => !is_null($user->email_verified_at),
                'last_login_at' => $user->last_login_at?->toDateTimeString() ?? '—',
                'last_login_ip' => $user->last_login_ip ?? '—',
                'login_count'   => $user->login_logs_count,
                'device_count'  => $user->trusted_devices_count,
                'is_blocked'    => in_array($user->id, $blockedUserIds),
                'created_at'    => $user->created_at->toDateTimeString(),
            ]);

        return response()->json($users);
    }

    // ── IP Blacklist ───────────────────────────────────────────────────────

    public function ipBlacklist(): JsonResponse
    {
        $list = IpBlacklist::latest()->get()->map(fn($r) => [
            'id'            => $r->id,
            'ip_address'    => $r->ip_address,
            'reason'        => $r->reason ?? '—',
            'blocked_by'    => $r->blocked_by,
            'block_count'   => $r->block_count,
            'blocked_until' => $r->blocked_until?->toDateTimeString() ?? 'Permanen',
            'blocked_at'    => $r->blocked_at->toDateTimeString(),
            'is_active'     => $r->isActive(),
        ]);

        return response()->json($list);
    }

    public function addIpBlacklist(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason'     => 'nullable|string|max:255',
            'minutes'    => 'nullable|integer|min:1',
        ]);

        $record = $this->blockingService->blacklistIp(
            $request->ip_address,
            $request->reason ?? 'Manual block',
            $request->minutes,
            'admin'
        );

        return response()->json(['success' => true, 'message' => "IP {$request->ip_address} ditambahkan ke blacklist.", 'data' => $record]);
    }

    public function removeIpBlacklist(string $ip): JsonResponse
    {
        $this->blockingService->unblacklistIp($ip);
        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari blacklist."]);
    }

    // ── IP Whitelist ───────────────────────────────────────────────────────

    public function ipWhitelist(): JsonResponse
    {
        $list = IpWhitelist::latest()->get()->map(fn($r) => [
            'id'         => $r->id,
            'ip_address' => $r->ip_address,
            'label'      => $r->label ?? '—',
            'added_by'   => $r->added_by ?? '—',
            'created_at' => $r->created_at->toDateTimeString(),
        ]);

        return response()->json($list);
    }

    public function addIpWhitelist(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'label'      => 'nullable|string|max:255',
        ]);

        $record = $this->blockingService->whitelistIp(
            $request->ip_address,
            $request->label ?? '',
            'admin'
        );

        return response()->json(['success' => true, 'message' => "IP {$request->ip_address} ditambahkan ke whitelist.", 'data' => $record]);
    }

    public function removeIpWhitelist(string $ip): JsonResponse
    {
        $this->blockingService->removeFromWhitelist($ip);
        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari whitelist."]);
    }

    // ── User Block ─────────────────────────────────────────────────────────

    public function unblockUser(int $userId): JsonResponse
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($user) {
            $this->blockingService->unblockUser($user->id, 'admin');

            // Clear cache failed attempts
            Cache::forget("block_count:user:{$user->id}");
            LoginLog::where('user_id', $user->id)
                ->where('decision', LoginLog::DECISION_BLOCK)
                ->orderBy('occurred_at', 'desc')
                ->limit(10)->get()->each->delete();

            TrustedDevice::where('user_id', $user->id)
                ->where('is_revoked', true)
                ->update(['is_revoked' => false]);

            $user->update(['is_active' => true]);
        });

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil di-unblock."]);
    }

    public function blockUserManual(Request $request, int $userId): JsonResponse
    {
        $request->validate(['minutes' => 'nullable|integer|min:1', 'reason' => 'nullable|string']);

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        $this->blockingService->blockUser($userId, $request->reason ?? 'Manual block', $request->minutes, 'admin');

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil diblokir."]);
    }

    // ── Device ────────────────────────────────────────────────────────────

    public function revokeDevice(int $deviceId): JsonResponse
    {
        $device = TrustedDevice::with('user:id,name,email')->find($deviceId);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan.'], 404);
        }

        $newState = !$device->is_revoked;
        $device->update(['is_revoked' => $newState]);
        Cache::forget("device_blocked:{$device->fingerprint_hash}");

        $action = $newState ? 'direvoke' : 'di-restore';
        return response()->json([
            'success'    => true,
            'is_revoked' => $newState,
            'message'    => "Device {$action} untuk user {$device->user?->name}.",
        ]);
    }
}