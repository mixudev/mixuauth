<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\IpBlacklist;
use App\Services\Auth\BlockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * DEV ONLY
 *
 * CRUD for the IP blacklist.
 * Delegates block/unblock logic to BlockingService to stay DRY.
 */
class DevIpBlacklistController extends Controller
{
    private const PAGE_SIZE = 50;

    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $search = $request->input('search');

        $rows = IpBlacklist::select([
                'id', 'ip_address', 'reason', 'blocked_by',
                'block_count', 'blocked_until', 'blocked_at',
            ])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) =>
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('reason',   'like', "%{$search}%")
            )
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $now  = now();
        $data = $rows->map(fn($r) => [
            'id'            => $r->id,
            'ip_address'    => $r->ip_address,
            'reason'        => $r->reason ?? '—',
            'blocked_by'    => $r->blocked_by,
            'block_count'   => $r->block_count,
            'blocked_until' => $r->blocked_until?->toDateTimeString() ?? 'Permanen',
            'blocked_at'    => $r->blocked_at->toDateTimeString(),
            'is_active'     => is_null($r->blocked_until) || $r->blocked_until > $now,
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function store(Request $request): JsonResponse
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

        Cache::forget('dev_monitor_stats');

        return response()->json([
            'success' => true,
            'message' => "IP {$request->ip_address} ditambahkan ke blacklist.",
            'data'    => $record,
        ]);
    }

    public function destroy(string $ip): JsonResponse
    {
        $this->blockingService->unblacklistIp($ip);
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari blacklist."]);
    }
}
