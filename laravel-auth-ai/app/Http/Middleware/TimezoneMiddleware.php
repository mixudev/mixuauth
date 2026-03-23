<?php

namespace App\Http\Middleware;

use App\Services\TimezoneService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimezoneMiddleware
{
    public function __construct(
        private readonly TimezoneService $timezoneService
    ) {}

    /**
     * Deteksi timezone user dari berbagai sumber dan set untuk request ini.
     *
     * Prioritas sumber timezone:
     *  1. Header X-Timezone  → dikirim oleh JavaScript saat page load
     *  2. Session            → preferensi yang sudah tersimpan sebelumnya
     *  3. Kolom DB user      → dihandle otomatis di TimezoneService::getUserTimezone()
     *  4. UTC                → fallback aman
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $request->header('X-Timezone');

        // Hanya set dari header jika valid — cegah injection
        if ($timezone && $this->timezoneService->isValid($timezone)) {
            $this->timezoneService->setUserTimezone($timezone);
        }

        return $next($request);
    }
}
