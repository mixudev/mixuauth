<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Models\UserBlock;
use App\Services\Auth\BlockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DEV ONLY
 *
 * Lists users and provides manual block/unblock operations.
 *
 * Block cache (dev_blocked_user_ids) has a 15-second TTL.
 * Unblock is wrapped in a transaction to keep log + device state consistent.
 */
class DevUserController extends Controller
{
    private const PAGE_SIZE = 50;

    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');

        // Cache for 15s to avoid re-querying on every paginated request.
        $blockedIds = Cache::remember('dev_blocked_user_ids', 15, fn() =>
            UserBlock::active()->pluck('user_id')->flip()->toArray()
        );

        $rows = User::select([
                'id', 'name', 'email', 'is_active',
                'email_verified_at', 'last_login_at',
                'last_login_ip', 'created_at',
            ])
            ->withCount(['loginLogs as login_count', 'trustedDevices as device_count'])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('name',   'like', "%{$search}%")
                   ->orWhere('email','like', "%{$search}%")
            ))
            ->when($status === 'blocked', fn($q) => $q->whereIn('id', array_keys($blockedIds)))
            ->when($status === 'ok',      fn($q) => $q->whereNotIn('id', array_keys($blockedIds)))
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'email'         => $u->email,
            'is_active'     => $u->is_active,
            'verified'      => !is_null($u->email_verified_at),
            'last_login_at' => $u->last_login_at?->toDateTimeString() ?? '—',
            'last_login_ip' => $u->last_login_ip ?? '—',
            'login_count'   => $u->login_count,
            'device_count'  => $u->device_count,
            'created_at'    => $u->created_at->toDateTimeString(),
            'is_blocked'    => isset($blockedIds[$u->id]),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function unblock(int $userId): JsonResponse
    {
        $user = User::select('id', 'name')->find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($user) {
            $this->blockingService->unblockUser($user->id, 'admin');
            Cache::forget("block_count:user:{$user->id}");

            // Remove latest 10 blocked login logs to keep audit log clean.
            LoginLog::where('user_id', $user->id)
                ->where('decision', LoginLog::DECISION_BLOCK)
                ->orderByDesc('occurred_at')
                ->limit(10)
                ->delete();

            // Restore all revoked devices for this user.
            TrustedDevice::where('user_id', $user->id)
                ->where('is_revoked', true)
                ->update(['is_revoked' => false]);

            $user->update(['is_active' => true]);
        });

        Cache::forget('dev_blocked_user_ids');
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil di-unblock."]);
    }

    public function block(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'minutes' => 'nullable|integer|min:1',
            'reason'  => 'nullable|string|max:255',
        ]);

        $user = User::select('id', 'name')->find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }

        $this->blockingService->blockUser(
            $userId,
            $request->reason ?? 'Manual block via dev monitor',
            $request->minutes,
            'admin'
        );

        Cache::forget('dev_blocked_user_ids');
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "User {$user->name} berhasil diblokir."]);
    }
}
