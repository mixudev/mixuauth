<?php

use App\Services\TimezoneService;
use Carbon\Carbon;

if (! function_exists('local_time')) {
    function local_time($date, string $format = 'd M Y, H:i', ?string $timezone = null): string
    {
        return app(TimezoneService::class)->format($date, $format, $timezone);
    }
}

if (! function_exists('user_tz')) {
    function user_tz(): string
    {
        return app(TimezoneService::class)->getUserTimezone();
    }
}

if (! function_exists('to_local')) {
    function to_local($date, ?string $timezone = null): Carbon
    {
        return app(TimezoneService::class)->toLocal($date, $timezone);
    }
}