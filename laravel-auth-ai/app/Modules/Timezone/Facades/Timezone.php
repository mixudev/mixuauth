<?php

namespace App\Modules\Timezone\Facades;

use Illuminate\Support\Facades\Facade;
use App\Modules\Timezone\Services\TimezoneService;

/**
 * @method static string getUserTimezone()
 * @method static void setUserTimezone(string $timezone)
 * @method static void saveUserTimezone(string $timezone)
 * @method static bool isValid(string $timezone)
 * @method static \Carbon\Carbon toLocal(\Carbon\Carbon $date, ?string $timezone = null)
 * @method static \Carbon\Carbon toUtc(\Carbon\Carbon $date)
 * @method static string format(\Carbon\Carbon $date, string $format = 'd M Y, H:i', ?string $timezone = null)
 * @method static string diffForHumans(\Carbon\Carbon $date, ?string $timezone = null)
 * @method static array allTimezones()
 * @method static array groupedTimezones()
 * 
 * @see TimezoneService
 */
class Timezone extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TimezoneService::class;
    }
}
