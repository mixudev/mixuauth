<?php

namespace App\Services\Auth;

use App\Models\IpBlacklist;
use App\Models\IpWhitelist;
use App\Models\LoginLog;
use App\Models\TrustedDevice;
use App\Models\UserBlock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockingService
{
    /*
    |--------------------------------------------------------------------------
    | Layanan pengelolaan block/whitelist untuk IP, perangkat, dan user.
    |
    | Urutan pemeriksaan di login:
    | 1. IP whitelist → langsung ALLOW (skip AI)
    | 2. IP blacklist → langsung BLOCK
    | 3. Device blacklist → langsung BLOCK
    | 4. User block → langsung BLOCK
    |--------------------------------------------------------------------------
    */

    // Berapa kali keputusan BLOCK dari AI sebelum auto-lock user
    private const AUTO_LOCK_USER_AFTER  = 3;
    // Berapa kali keputusan BLOCK dari IP sebelum auto-blacklist IP
    private const AUTO_BLOCK_IP_AFTER   = 5;
    // Durasi default block sementara (menit)
    private const DEFAULT_BLOCK_MINUTES = 60;

    // -----------------------------------------------------------------------
    // Pemeriksaan Pre-Login
    // -----------------------------------------------------------------------

    /**
     * Periksa apakah IP ada di whitelist.
     * IP whitelist selalu diizinkan masuk tanpa evaluasi AI.
     */
    public function isIpWhitelisted(string $ip): bool
    {
        $cacheKey = "ip_whitelist:{$ip}";

        return Cache::remember($cacheKey, 300, fn() =>
            IpWhitelist::where('ip_address', $ip)->exists()
        );
    }

    /**
     * Periksa apakah IP sedang diblokir.
     */
    public function isIpBlocked(string $ip): bool
    {
        $cacheKey = "ip_blocked:{$ip}";

        return Cache::remember($cacheKey, 60, fn() =>
            IpBlacklist::where('ip_address', $ip)->active()->exists()
        );
    }

    /**
     * Periksa apakah fingerprint perangkat sedang diblokir.
     */
    public function isDeviceBlocked(string $fingerprint): bool
    {
        $cacheKey = "device_blocked:{$fingerprint}";

        return Cache::remember($cacheKey, 60, fn() =>
            TrustedDevice::where('fingerprint_hash', $fingerprint)
                ->where('is_revoked', true)
                ->exists()
        );
    }

    /**
     * Periksa apakah user sedang diblokir.
     */
    public function isUserBlocked(int $userId): bool
    {
        $cacheKey = "user_blocked:{$userId}";

        return Cache::remember($cacheKey, 60, fn() =>
            UserBlock::where('user_id', $userId)->active()->exists()
        );
    }

    // -----------------------------------------------------------------------
    // Auto-Block setelah keputusan BLOCK dari AI
    // -----------------------------------------------------------------------

    /**
     * Dipanggil setiap kali AI memutuskan BLOCK.
     * Evaluasi apakah perlu auto-lock user atau auto-blacklist IP.
     */
    public function handleBlockDecision(int $userId, string $ip, string $fingerprint): void
    {
        $this->incrementBlockCounter($userId, $ip);
        $this->maybeAutoLockUser($userId);
        $this->maybeAutoBlacklistIp($ip);
        $this->revokeDevice($fingerprint, $userId);
    }

    // -----------------------------------------------------------------------
    // Manajemen IP Blacklist
    // -----------------------------------------------------------------------

    public function blacklistIp(string $ip, string $reason = 'manual', int $minutes = null, string $by = 'admin'): IpBlacklist
    {
        $blockedUntil = $minutes ? now()->addMinutes($minutes) : null;

        $record = IpBlacklist::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'       => $reason,
                'blocked_by'   => $by,
                'blocked_until' => $blockedUntil,
                'blocked_at'   => now(),
            ]
        );

        // Increment counter jika sudah ada
        $record->increment('block_count');
        Cache::forget("ip_blocked:{$ip}");

        Log::channel('security')->warning('IP diblokir', [
            'ip'            => $ip,
            'reason'        => $reason,
            'blocked_until' => $blockedUntil,
            'by'            => $by,
        ]);

        return $record;
    }

    public function unblacklistIp(string $ip): bool
    {
        $deleted = IpBlacklist::where('ip_address', $ip)->delete();
        Cache::forget("ip_blocked:{$ip}");

        Log::channel('security')->info('IP dihapus dari blacklist', ['ip' => $ip]);

        return $deleted > 0;
    }

    // -----------------------------------------------------------------------
    // Manajemen IP Whitelist
    // -----------------------------------------------------------------------

    public function whitelistIp(string $ip, string $label = '', string $by = 'admin'): IpWhitelist
    {
        $record = IpWhitelist::updateOrCreate(
            ['ip_address' => $ip],
            ['label' => $label, 'added_by' => $by]
        );

        Cache::forget("ip_whitelist:{$ip}");

        Log::channel('security')->info('IP ditambahkan ke whitelist', [
            'ip'    => $ip,
            'label' => $label,
            'by'    => $by,
        ]);

        return $record;
    }

    public function removeFromWhitelist(string $ip): bool
    {
        $deleted = IpWhitelist::where('ip_address', $ip)->delete();
        Cache::forget("ip_whitelist:{$ip}");

        return $deleted > 0;
    }

    // -----------------------------------------------------------------------
    // Manajemen User Block
    // -----------------------------------------------------------------------

    public function blockUser(int $userId, string $reason = 'manual', int $minutes = null, string $by = 'admin'): UserBlock
    {
        $blockedUntil = $minutes ? now()->addMinutes($minutes) : null;

        // Tutup block lama jika ada
        UserBlock::where('user_id', $userId)
            ->whereNull('unblocked_at')
            ->update(['unblocked_at' => now(), 'unblocked_by' => 'superseded']);

        $block = UserBlock::create([
            'user_id'       => $userId,
            'reason'        => $reason,
            'blocked_by'    => $by,
            'blocked_until' => $blockedUntil,
            'block_count'   => $this->getUserBlockCount($userId) + 1,
        ]);

        Cache::forget("user_blocked:{$userId}");

        Log::channel('security')->warning('User diblokir', [
            'user_id'       => $userId,
            'reason'        => $reason,
            'blocked_until' => $blockedUntil,
            'by'            => $by,
        ]);

        return $block;
    }

    public function unblockUser(int $userId, string $by = 'admin'): bool
    {
        $updated = UserBlock::where('user_id', $userId)
            ->whereNull('unblocked_at')
            ->update([
                'unblocked_at' => now(),
                'unblocked_by' => $by,
            ]);

        Cache::forget("user_blocked:{$userId}");
        Cache::forget("block_count:user:{$userId}");

        Log::channel('security')->info('User di-unblock', [
            'user_id' => $userId,
            'by'      => $by,
        ]);

        return $updated > 0;
    }

    // -----------------------------------------------------------------------
    // Manajemen Device Block
    // -----------------------------------------------------------------------

    public function revokeDevice(string $fingerprint, int $userId): void
    {
        TrustedDevice::where('fingerprint_hash', $fingerprint)
            ->where('user_id', $userId)
            ->update(['is_revoked' => true]);

        Cache::forget("device_blocked:{$fingerprint}");
    }

    public function restoreDevice(string $fingerprint, int $userId): void
    {
        TrustedDevice::where('fingerprint_hash', $fingerprint)
            ->where('user_id', $userId)
            ->update(['is_revoked' => false]);

        Cache::forget("device_blocked:{$fingerprint}");
    }

    // -----------------------------------------------------------------------
    // Private Helpers
    // -----------------------------------------------------------------------

    private function incrementBlockCounter(int $userId, string $ip): void
    {
        $userKey = "block_count:user:{$userId}";
        $ipKey   = "block_count:ip:{$ip}";

        Cache::increment($userKey);
        Cache::put($userKey, Cache::get($userKey, 1), now()->addHours(24));

        Cache::increment($ipKey);
        Cache::put($ipKey, Cache::get($ipKey, 1), now()->addHours(24));
    }

    private function maybeAutoLockUser(int $userId): void
    {
        $count = (int) Cache::get("block_count:user:{$userId}", 0);

        if ($count >= self::AUTO_LOCK_USER_AFTER) {
            $alreadyBlocked = UserBlock::where('user_id', $userId)->active()->exists();

            if (!$alreadyBlocked) {
                $this->blockUser(
                    $userId,
                    reason: "Auto-lock: {$count} keputusan BLOCK dalam 24 jam",
                    minutes: self::DEFAULT_BLOCK_MINUTES,
                    by: 'auto'
                );
            }
        }
    }

    private function maybeAutoBlacklistIp(string $ip): void
    {
        $count = (int) Cache::get("block_count:ip:{$ip}", 0);

        if ($count >= self::AUTO_BLOCK_IP_AFTER) {
            $alreadyBlocked = IpBlacklist::where('ip_address', $ip)->active()->exists();

            if (!$alreadyBlocked) {
                $this->blacklistIp(
                    $ip,
                    reason: "Auto-block: {$count} keputusan BLOCK dalam 24 jam",
                    minutes: self::DEFAULT_BLOCK_MINUTES,
                    by: 'auto'
                );
            }
        }
    }

    private function getUserBlockCount(int $userId): int
    {
        return UserBlock::where('user_id', $userId)->count();
    }
}
