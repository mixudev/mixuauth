<?php

namespace App\Modules\Security\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DeviceIdentifierMiddleware
{
    public const COOKIE_NAME = 'device_trust_token';

    /**
     * Pastikan setiap pengunjung memiliki ID perangkat (device_trust_id) yang stabil.
     * ID ini akan digunakan sebagai basis fingerprinting untuk Trusted Device.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceToken = $request->cookie(self::COOKIE_NAME) ?: $request->cookie('device_trust_id');
        $hasExistingToken = ! empty($deviceToken);

        if (! $hasExistingToken) {
            $deviceToken = Str::random(64);
            $request->cookies->add([self::COOKIE_NAME => $deviceToken]);
        }

        $response = $next($request);

        if (! $hasExistingToken && method_exists($response, 'withCookie')) {
            $response->withCookie(cookie(
                self::COOKIE_NAME,
                $deviceToken,
                (int) config('security.session.trusted_device_cookie_minutes', 60 * 24 * 30),
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'Lax'
            ));
        }

        return $response;
    }
}
