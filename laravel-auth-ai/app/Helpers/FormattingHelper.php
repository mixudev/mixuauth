<?php

use App\Modules\Common\Services\FormattingService;
use App\Modules\Timezone\Services\TimezoneService;
use Carbon\Carbon;

if (! function_exists('short_number')) {
    function short_number(int|float $number): string
    {
        return app(FormattingService::class)->shortNumber($number);
    }
}

if (! function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        return app(FormattingService::class)->formatBytes($bytes, $precision);
    }
}

if (! function_exists('format_money')) {
    function format_money(int|float $amount, string $currency = 'IDR'): string
    {
        return app(FormattingService::class)->formatMoney($amount, $currency);
    }
}

if (! function_exists('local_time')) {
    /**
     * Helper global untuk mendapatkan waktu lokal (kompatibilitas lama).
     */
    function local_time($date, string $format = 'd M Y, H:i'): string
    {
        if (! $date) return '—';
        
        if (! $date instanceof Carbon) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                return '—';
            }
        }

        return app(TimezoneService::class)->format($date, $format);
    }
}
