<?php

namespace App\Modules\Dashboard\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Modules\Security\Models\LoginLog;
use App\Modules\Security\Models\TrustedDevice;
use App\Modules\Security\Models\IpBlacklist;
use App\Modules\Security\Models\IpWhitelist;
use App\Modules\Authentication\Models\OtpVerification;
use App\Modules\Identity\Models\UserBlock;
use App\Modules\Security\Models\SecurityNotification;

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
