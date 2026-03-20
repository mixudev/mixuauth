<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * DEV ONLY
 *
 * Lists login attempt logs with cursor-based pagination.
 * Also provides a streaming CSV export for large datasets.
 *
 * Recommended indexes:
 *   login_logs: (id DESC), (decision, id DESC), (status, id DESC)
 */
class DevLoginLogController extends Controller
{
    private const PAGE_SIZE    = 50;
    private const EXPORT_CHUNK = 1000;

    public function index(Request $request): JsonResponse
    {
        $cursor   = $request->integer('cursor', 0);
        $status   = $request->input('status');
        $decision = $request->input('decision');
        $search   = $request->input('search');

        $rows = LoginLog::select([
                'login_logs.id',
                'login_logs.ip_address',
                'login_logs.device_fingerprint',
                'login_logs.status',
                'login_logs.decision',
                'login_logs.risk_score',
                'login_logs.reason_flags',
                'login_logs.occurred_at',
                'login_logs.email_attempted',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->leftJoin('users', 'users.id', '=', 'login_logs.user_id')
            ->when($cursor > 0, fn($q) => $q->where('login_logs.id', '<', $cursor))
            ->when($status,   fn($q) => $q->where('login_logs.status',   $status))
            ->when($decision, fn($q) => $q->where('login_logs.decision', strtoupper($decision)))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',              'like', "%{$search}%")
                   ->orWhere('login_logs.ip_address', 'like', "%{$search}%")
                   ->orWhere('users.email',           'like', "%{$search}%")
            ))
            ->orderByDesc('login_logs.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($l) => [
            'id'           => $l->id,
            'user'         => $l->user_name ?? 'Unknown',
            'email'        => $l->user_email ?? $l->email_attempted ?? '—',
            'ip_address'   => $l->ip_address,
            'device_fp'    => $l->device_fingerprint ? substr($l->device_fingerprint, 0, 16) . '…' : '—',
            'status'       => $l->status,
            'decision'     => $l->decision ?? '—',
            'risk_score'   => $l->risk_score,
            'reason_flags' => $l->reason_flags,
            'occurred_at'  => $l->occurred_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $decision = $request->input('decision');

        return response()->streamDownload(function () use ($decision) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Email', 'IP', 'Status', 'Decision', 'Risk', 'Occurred At']);

            LoginLog::select([
                    'login_logs.id',
                    'login_logs.ip_address',
                    'login_logs.status',
                    'login_logs.decision',
                    'login_logs.risk_score',
                    'login_logs.occurred_at',
                    'users.name  as user_name',
                    'users.email as user_email',
                ])
                ->leftJoin('users', 'users.id', '=', 'login_logs.user_id')
                ->when($decision, fn($q) => $q->where('decision', strtoupper($decision)))
                ->orderByDesc('login_logs.id')
                ->chunk(self::EXPORT_CHUNK, function ($rows) use ($handle) {
                    foreach ($rows as $l) {
                        fputcsv($handle, [
                            $l->id, $l->user_name, $l->user_email,
                            $l->ip_address, $l->status, $l->decision,
                            $l->risk_score, $l->occurred_at,
                        ]);
                    }
                    ob_flush();
                    flush();
                });

            fclose($handle);
        }, 'login_logs_' . now()->format('Ymd_His') . '.csv');
    }
}
