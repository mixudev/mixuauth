<?php

namespace App\Http\Controllers\Admin\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * DEV ONLY
 *
 * Returns aggregated counts for the monitoring dashboard stats bar.
 * Uses a single SQL subquery to avoid N+1 round-trips.
 * Cached for STATS_TTL seconds to prevent hammering on live-reload.
 */
class DevStatsController extends Controller
{
    private const STATS_TTL = 10; // seconds

    public function __invoke(): JsonResponse
    {
        $data = Cache::remember('dev_monitor_stats', self::STATS_TTL, function () {
            $row = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM users)                                                AS users,
                    (SELECT COUNT(*) FROM users WHERE is_active = 1)                           AS active_users,
                    (SELECT COUNT(*) FROM login_logs)                                          AS total_logs,
                    (SELECT COUNT(*) FROM login_logs WHERE decision = 'BLOCK')                 AS blocked_logs,
                    (SELECT COUNT(*) FROM otp_verifications
                        WHERE expires_at > NOW() AND verified_at IS NULL)                      AS active_otps,
                    (SELECT COUNT(*) FROM trusted_devices WHERE is_revoked = 0)                AS trusted_devices,
                    (SELECT COUNT(*) FROM ip_blacklists
                        WHERE blocked_until IS NULL OR blocked_until > NOW())                  AS ip_blacklisted,
                    (SELECT COUNT(*) FROM ip_whitelists)                                       AS ip_whitelisted,
                    (SELECT COUNT(*) FROM user_blocks
                        WHERE blocked_until IS NULL OR blocked_until > NOW())                  AS users_blocked
            ");

            return (array) $row;
        });

        return response()->json($data);
    }
}
