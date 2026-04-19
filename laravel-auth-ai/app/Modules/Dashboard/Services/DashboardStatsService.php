<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Security\Models\LoginLog;
use App\Modules\Security\Models\IpBlacklist;
use App\Modules\Identity\Models\UserBlock;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Security\Models\TrustedDevice;
use App\Models\FailedJob;
use App\Modules\Security\Models\SecurityNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class DashboardStatsService
{
    /**
     * Resolve Carbon "since" dari period string.
     * Dipanggil di banyak method — jangan inline biar konsisten.
     */
    private function since(string $period): Carbon
    {
        return match ($period) {
            '24h'  => now()->subDay(),
            '30d'  => now()->subDays(30),
            default => now()->subDays(7),   // '7d'
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  STAT CARDS
    //  TTL: 5 menit — cukup untuk dashboard realtime tanpa overload DB
    //  Semua query ini memanfaatkan index: occurred_at, status, ip_address
    // ──────────────────────────────────────────────────────────────────────────

    public function getCardStats(string $period): array
    {
        return Cache::remember("dash:stats:{$period}", 300, function () use ($period) {
            $since = $this->since($period);

            // Ambil aggregate dalam SATU query untuk efisiensi
            $loginAgg = LoginLog::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count
            ")
            ->where('occurred_at', '>=', $since)
            ->first();

            // Hitung login trend vs periode sebelumnya (untuk badge ▲▼)
            $prevSince   = $since->copy()->sub($since->diffAsCarbonInterval(now()));
            $prevTotal   = LoginLog::where('occurred_at', '>=', $prevSince)
                                   ->where('occurred_at', '<', $since)
                                   ->count();
            $loginTrend  = $prevTotal > 0
                ? round((($loginAgg->total - $prevTotal) / $prevTotal) * 100, 1)
                : null;

            return [
                'total_logins'   => $loginAgg->total ?? 0,
                'success_logins' => $loginAgg->success_count ?? 0,
                'login_trend'    => $loginTrend,

                // IP Blacklist: aktif (permanen + belum expired)
                'blocked_ips'    => IpBlacklist::where(function ($q) {
                    $q->whereNull('blocked_until')
                      ->orWhere('blocked_until', '>', now());
                })->count(),

                // User block aktif (belum di-unblock & belum expired)
                'blocked_users'  => UserBlock::whereNull('unblocked_at')
                    ->where(function ($q) {
                        $q->whereNull('blocked_until')
                          ->orWhere('blocked_until', '>', now());
                    })->count(),

                // OTP aktif: belum verified & belum expired
                'active_otps'    => OtpVerification::whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->count(),

                'failed_jobs'    => FailedJob::count(),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  DECISION BREAKDOWN
    //  Hasil keputusan AI: ALLOW / OTP / BLOCK / PENDING / FALLBACK
    // ──────────────────────────────────────────────────────────────────────────

    public function getDecisionBreakdown(string $period): array
    {
        return Cache::remember("dash:decision:{$period}", 300, function () use ($period) {
            $since = $this->since($period);

            $rows = LoginLog::selectRaw('decision, COUNT(*) as total')
                ->where('occurred_at', '>=', $since)
                ->whereNotNull('decision')
                ->groupBy('decision')
                ->pluck('total', 'decision')
                ->toArray();

            // Pastikan semua key selalu ada agar Blade & Chart.js tidak error
            return array_merge([
                'ALLOW'    => 0,
                'OTP'      => 0,
                'BLOCK'    => 0,
                'PENDING'  => 0,
                'FALLBACK' => 0,
            ], $rows);
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  OTP SUMMARY
    // ──────────────────────────────────────────────────────────────────────────

    public function getOtpSummary(): array
    {
        return Cache::remember('dash:otp:summary', 300, function () {
            return [
                'active'   => OtpVerification::whereNull('verified_at')
                                ->where('expires_at', '>', now())
                                ->count(),
                'verified' => OtpVerification::whereNotNull('verified_at')->count(),
                'expired'  => OtpVerification::whereNull('verified_at')
                                ->where('expires_at', '<=', now())
                                ->count(),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  TRUSTED DEVICE SUMMARY
    // ──────────────────────────────────────────────────────────────────────────

    public function getDeviceSummary(): array
    {
        return Cache::remember('dash:devices:summary', 300, function () {
            return [
                'total'   => TrustedDevice::where('is_revoked', false)
                               ->where(function ($q) {
                                   $q->whereNull('trusted_until')
                                     ->orWhere('trusted_until', '>', now());
                               })->count(),
                'expired' => TrustedDevice::where('is_revoked', false)
                               ->whereNotNull('trusted_until')
                               ->where('trusted_until', '<=', now())
                               ->count(),
                'revoked' => TrustedDevice::where('is_revoked', true)->count(),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  NOTIFICATIONS
    // ──────────────────────────────────────────────────────────────────────────

    public function getUnreadNotificationCount(): int
    {
        return Cache::remember('dash:notif:unread_count', 60, fn () =>
            SecurityNotification::unread()->count()
        );
    }

    /**
     * Alert kritis = type error yang belum dibaca dalam 1 jam terakhir
     */
    public function getCriticalAlerts()
    {
        return Cache::remember('dash:notif:critical', 60, fn () =>
            SecurityNotification::unread()
                ->where('type', 'error')
                ->where('created_at', '>=', now()->subHour())
                ->orderByDesc('created_at')
                ->limit(3)
                ->get()
        );
    }

    public function getRecentNotifications()
    {
        return Cache::remember('dash:notif:recent', 60, fn () =>
            SecurityNotification::unread()
                ->orderByDesc('created_at')
                ->limit(4)
                ->get()
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  RECENT LOGS
    //  PENTING: selalu pakai select() eksplisit — jangan select(*) di tabel besar
    //  Index yang dipakai: occurred_at (DESC)
    // ──────────────────────────────────────────────────────────────────────────

    public function getRecentLogs()
    {
        // Recent logs TIDAK di-cache karena harus selalu realtime
        return LoginLog::with(['user:id,name,email'])
            ->select([
                'id', 'user_id', 'email_attempted',
                'ip_address', 'country_code',
                'status', 'decision', 'risk_score',
                'occurred_at',
            ])
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  TOP THREAT IPs
    //  Index yang dipakai: ip_address, occurred_at, status
    // ──────────────────────────────────────────────────────────────────────────

    public function getTopThreatIps(string $period)
    {
        return Cache::remember("dash:topips:{$period}", 300, function () use ($period) {
            $since = $this->since($period);

            return LoginLog::selectRaw('
                ip_address,
                COUNT(*) as attempts,
                SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked_count,
                MAX(risk_score) as max_risk
            ')
            ->where('occurred_at', '>=', $since)
            ->where('status', 'blocked')
            ->groupBy('ip_address')
            ->orderByDesc('attempts')
            ->limit(8)
            ->get();
        });
    }
}
