<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\IpWhitelist;
use App\Services\Auth\BlockingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * DEV ONLY
 *
 * CRUD for the IP whitelist.
 */
class DevIpWhitelistController extends Controller
{
    private const PAGE_SIZE = 50;

    public function __construct(
        private readonly BlockingService $blockingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $search = $request->input('search');

        $rows = IpWhitelist::select(['id', 'ip_address', 'label', 'added_by', 'created_at'])
            ->when($cursor > 0, fn($q) => $q->where('id', '<', $cursor))
            ->when($search, fn($q) =>
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('label',    'like', "%{$search}%")
            )
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($r) => [
            'id'         => $r->id,
            'ip_address' => $r->ip_address,
            'label'      => $r->label ?? '—',
            'added_by'   => $r->added_by ?? '—',
            'created_at' => $r->created_at->toDateTimeString(),
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
            'label'      => 'nullable|string|max:255',
        ]);

        $record = $this->blockingService->whitelistIp(
            $request->ip_address,
            $request->label ?? '',
            'admin'
        );

        Cache::forget('dev_monitor_stats');

        return response()->json([
            'success' => true,
            'message' => "IP {$request->ip_address} ditambahkan ke whitelist.",
            'data'    => $record,
        ]);
    }

    public function destroy(string $ip): JsonResponse
    {
        $this->blockingService->removeFromWhitelist($ip);
        Cache::forget('dev_monitor_stats');

        return response()->json(['success' => true, 'message' => "IP {$ip} dihapus dari whitelist."]);
    }
}
