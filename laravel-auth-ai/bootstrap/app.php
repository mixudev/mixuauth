<?php

use App\Modules\Authentication\Middleware\VerifySessionFingerprintMiddleware;
use App\Modules\Authentication\Middleware\EnsureSessionVersionMiddleware;
use App\Modules\Authentication\Middleware\PreAuthRateLimitMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap Aplikasi Laravel 11
|--------------------------------------------------------------------------
| Laravel 11 menggunakan file ini sebagai pengganti Kernel.php.
| Middleware global, route, dan exception handler dikonfigurasi di sini.
|--------------------------------------------------------------------------
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');

        // Middleware global untuk semua request API
        $middleware->api(append: [
            // Header keamanan dasar ditangani oleh reverse proxy (Nginx/Traefik)
        ]);

        // Daftarkan alias middleware — sekarang menggunakan namespace modul
        $middleware->alias([
            'pre.auth.ratelimit'      => PreAuthRateLimitMiddleware::class,
            'verify.fingerprint'      => VerifySessionFingerprintMiddleware::class,
            'ensure.session.version'  => EnsureSessionVersionMiddleware::class,
            'role'                    => CheckRole::class,
            'permission'              => CheckPermission::class,
        ]);

        // Middleware web global
        $middleware->web(append: [
            \App\Modules\Security\Middleware\DeviceIdentifierMiddleware::class,
            \App\Modules\Timezone\Middleware\TimezoneMiddleware::class,
            \App\Modules\Security\Middleware\SecurityHeadersMiddleware::class, // [M-05 FIX]
        ]);

        $trustedProxies = array_values(array_filter(array_map(
            static fn (string $proxy) => trim($proxy),
            explode(',', (string) env('TRUSTED_PROXIES', '127.0.0.1,::1'))
        )));

        $middleware->trustProxies(at: $trustedProxies);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Kembalikan semua exception dalam format JSON untuk API
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return \App\Exceptions\ApiExceptionHandler::render($e);
            }
        });

    })

    ->withProviders([
        // ── Authentication Module ─────────────────────────────────────────────
        App\Modules\Authentication\AuthServiceProvider::class,

        // ── Feature Providers ─────────────────────────────────────────────────
        App\Modules\Security\SecurityServiceProvider::class,
        App\Modules\Identity\IdentityServiceProvider::class,
        App\Modules\Dashboard\DashboardServiceProvider::class,
        App\Modules\Authorization\AuthorizationServiceProvider::class,
        App\Modules\Timezone\TimezoneServiceProvider::class,
        App\Modules\Common\CommonServiceProvider::class,
        App\Modules\Communication\CommunicationServiceProvider::class,
    ])
    ->create();
