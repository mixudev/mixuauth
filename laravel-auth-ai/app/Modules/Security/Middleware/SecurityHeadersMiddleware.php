<?php

namespace App\Modules\Security\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | [M-05 FIX] Middleware untuk menyuntikkan Security Headers (termasuk CSP).
    |
    | Melindungi aplikasi dari serangan berbasis browser seperti:
    | - Cross-Site Scripting (XSS)
    | - Clickjacking
    | - MIME-type sniffing
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya terapkan untuk response yang dikirimkan dengan sukses
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
            
            // Konfigurasi Content Security Policy (CSP)
            $nonce = $this->getNonce();
            view()->share('cspNonce', $nonce); // Bagikan nonce ke view blade

            $csp = [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'", // Diizinkan untuk script dengan nonce khusus
                "style-src 'self' 'unsafe-inline' fonts.googleapis.com", 
                "font-src 'self' fonts.gstatic.com",
                "img-src 'self' data: https://ui-avatars.com", // Izinkan UI avatars
                "frame-ancestors 'none'",
                "object-src 'none'",
                "base-uri 'self'",
            ];

            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }

    /**
     * Dapatkan nonce untuk request saat ini.
     */
    private function getNonce(): string
    {
        if (! request()->hasMacro('cspNonce')) {
            $nonce = Str::random(40);
            request()->macro('cspNonce', fn() => $nonce);
        }

        return request()->cspNonce();
    }
}
