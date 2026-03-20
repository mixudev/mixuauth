<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DEV ONLY
 *
 * Lists OTP verification records with cursor-based pagination.
 *
 * Recommended indexes:
 *   otp_verifications: (id DESC), (expires_at, verified_at)
 */
class DevOtpController extends Controller
{
    private const PAGE_SIZE = 50;

    public function index(Request $request): JsonResponse
    {
        $cursor = $request->integer('cursor', 0);
        $status = $request->input('status');
        $search = $request->input('search');
        $now    = now();

        $rows = OtpVerification::select([
                'otp_verifications.id',
                'otp_verifications.token',
                'otp_verifications.attempts',
                'otp_verifications.expires_at',
                'otp_verifications.verified_at',
                'otp_verifications.created_at',
                'users.name  as user_name',
                'users.email as user_email',
            ])
            ->join('users', 'users.id', '=', 'otp_verifications.user_id')
            ->when($cursor > 0,          fn($q) => $q->where('otp_verifications.id', '<', $cursor))
            ->when($status === 'active',  fn($q) => $q->whereNull('verified_at')->where('expires_at', '>', $now))
            ->when($status === 'verified',fn($q) => $q->whereNotNull('verified_at'))
            ->when($status === 'expired', fn($q) => $q->whereNull('verified_at')->where('expires_at', '<=', $now))
            ->when($search, fn($q) => $q->where(fn($sq) =>
                $sq->where('users.name',   'like', "%{$search}%")
                   ->orWhere('users.email','like', "%{$search}%")
            ))
            ->orderByDesc('otp_verifications.id')
            ->limit(self::PAGE_SIZE + 1)
            ->get();

        $hasMore = $rows->count() > self::PAGE_SIZE;
        if ($hasMore) $rows->pop();

        $data = $rows->map(fn($o) => [
            'id'          => $o->id,
            'user'        => $o->user_name,
            'email'       => $o->user_email,
            'otp_code'    => $o->token ? substr($o->token, 0, 20) . '…' : '—',
            'status'      => $o->verified_at ? 'verified'
                           : ($o->expires_at && $o->expires_at < $now ? 'expired' : 'active'),
            'attempts'    => $o->attempts ?? 0,
            'expires_at'  => $o->expires_at?->toDateTimeString(),
            'verified_at' => $o->verified_at?->toDateTimeString(),
            'created_at'  => $o->created_at->toDateTimeString(),
        ]);

        return response()->json([
            'data'        => $data,
            'next_cursor' => $hasMore ? $rows->last()->id : null,
            'has_more'    => $hasMore,
        ]);
    }
}
