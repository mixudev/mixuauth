<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\TrustedDevice;

class StatsService
{
    public function get(): array
    {
        return Cache::remember(
            'dashboard.stats.raw',
            now()->addMinutes(5),
            fn () => [
                'users'       => User::count(),
                'loginLogs'   => LoginLog::count(),
                'whitelisted' => TrustedDevice::count(),
            ]
        );
    }
}