<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\TrustedDevice;
use App\Models\IpBlacklist;
use App\Models\IpWhitelist;
use App\Models\OtpVerification;
use App\Models\UserBlock;
use App\Models\SecurityNotification;

class StatsService
{
    public function get(): array
    {
        return Cache::remember(
            'dashboard.statscount.raw',
            now()->addMinutes(5),
            fn () => [
                'users'           => User::count(),
                'loginLogs'       => LoginLog::count(),
                'whitelisted'     => TrustedDevice::count(),
                'IpBlacklist'     => IpBlacklist::count(),
                'IpWhitelist'     => IpWhitelist::count(),
                'OtpCount'        => OtpVerification::count(),
                'UserBlock'       => UserBlock::count(),
                'securityNotifications' => SecurityNotification::unread()->count(),
                

            ]
        );
    }
}