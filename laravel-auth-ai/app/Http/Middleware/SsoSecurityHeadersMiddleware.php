<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SsoSecurityHeadersMiddleware
 * ----------------------------
 * Menambahkan HTTP Security Headers ke semua response SSO.
 * Melindungi dari: Clickjacking, MIME sniffing, XSS reflection, CSRF via Referrer,
 * dan protocol downgrade (HSTS).
 *
 * Daftarkan di SSOServiceProvider pada route group SSO.
 */
class SsoSecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Cegah konten dimuat dalam iframe (Clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Cegah browser menebak content-type (MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Batasi referrer yang dikirim ke request lintas-origin
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Paksa HTTPS selama 1 tahun (hanya di production)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy — ketat untuk halaman OAuth/SSO
        // Izinkan: self, font Google, FA CDN, dan inline style terbatas
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",            // inline JS di blade views
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",                        // redundan dengan X-Frame-Options, defense-in-depth
            "form-action 'self'",                            // form hanya boleh submit ke domain sendiri
            "base-uri 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Menonaktifkan fitur browser yang tidak diperlukan
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=()'
        );

        return $response;
    }
}
