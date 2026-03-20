<?php

use App\Http\Middleware\VerifySessionFingerprintMiddleware;
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

        // Middleware global untuk semua request API
        $middleware->api(append: [
            // Header keamanan dasar ditangani oleh reverse proxy (Nginx/Traefik)
            // Middleware tambahan dapat ditambahkan di sini sesuai kebutuhan
        ]);

        // Daftarkan alias middleware untuk kemudahan penggunaan di routes
        $middleware->alias([
            'pre.auth.ratelimit' => \App\Http\Middleware\PreAuthRateLimitMiddleware::class,
            'verify.fingerprint' => VerifySessionFingerprintMiddleware::class,
        ]);

        // Kecualikan route auth dari CSRF (untuk API stateless)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
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
        App\Providers\DashboardServiceProvider::class,
    ])
    ->create();
