<?php

namespace App\Providers;

// StatsService moved to DashboardServiceProvider
use App\Modules\Identity\Services\UserService;
use App\Models\User;
use App\Modules\Security\Models\SecurityNotification;
use App\Modules\Security\Models\TrustedDevice;
// TrustedDevicePolicy and SecurityNotificationPolicy moved to Security module
use App\Modules\Identity\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Passport\Passport;


class AppServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Application — Global Service Provider
    |
    | Binding yang bersifat lintas-modul (cross-module) dan konfigurasi
    | shared yang tidak dimiliki oleh satu modul tertentu.
    |
    | CATATAN: Binding services Auth, Security, dan Device telah dipindahkan
    | ke App\Modules\Authentication\AuthServiceProvider (Fase 1).
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        // ── Passport: Abaikan default routes bawaan Passport ──────────────────
        // WAJIB dipanggil di register() agar route default Passport (/oauth/authorize, dll)
        // tidak didaftarkan dan tidak bentrok dengan OAuthController kita.
        // Route /oauth/token tetap dihandle Passport via middleware Kernel.
        Passport::ignoreRoutes();

        // ── Shared / Cross-Module Services ────────────────────────────────────
        // Services ini digunakan oleh lebih dari satu modul, sehingga
        // didaftarkan secara global di sini.
        $this->app->singleton(UserService::class);
    }

    public function boot(): void
    {
        // ── Passport: Pastikan key path eksplisit ─────────────────────────────
        // Tanpa ini, Passport mencari key dari storage_path() secara default.
        // Kita eksplisitkan agar tidak bergantung pada working directory container.
        // Permission key harus 660 (bukan 777) agar Passport tidak menolaknya.
        Passport::loadKeysFrom(storage_path());

        // Paksa koneksi HTTPS di environment production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // ── RBAC Gate ─────────────────────────────────────────────────────────
        // Gate rules and Role/Permission policies have been moved to App\Modules\Authorization\AuthorizationServiceProvider

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\App\Modules\WaGateway\Models\WaGatewayConfig::class, \App\Modules\WaGateway\Policies\WaGatewayConfigPolicy::class);

        // ── Rate Limiters ─────────────────────────────────────────────────────
        RateLimiter::for('mfa', static function ($request) {
            $sessionToken = (string) $request->input('session_token', session('mfa_session_token', ''));
            $tokenKey     = $sessionToken !== '' ? hash('sha256', $sessionToken) : '';

            $email    = strtolower((string) $request->input('email', session('mfa_email', '')));
            $emailKey = $email !== '' ? hash('sha256', $email) : '';

            $key = $tokenKey !== ''
                ? "mfa|token:{$tokenKey}|ip:{$request->ip()}"
                : ($emailKey !== ''
                    ? "mfa|email:{$emailKey}|ip:{$request->ip()}"
                    : "mfa|ip:{$request->ip()}");

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('verification-send', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinutes(10, 3)->by($userId !== '' ? $userId : $request->ip());
        });

        RateLimiter::for('admin-actions', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinute(30)->by($userId !== '' ? $userId : $request->ip());
        });

        // Rate limiter untuk endpoint WA send — mencegah abuse kredit WA organisasi
        RateLimiter::for('wa-send', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinute(10)->by($userId !== '' ? $userId : $request->ip());
        });

        // Rate limiter untuk endpoint system health — mencegah trigger artisan berulang
        RateLimiter::for('system-health', static function ($request) {
            $userId = (string) optional($request->user())->id;
            return Limit::perMinute(1)->by($userId !== '' ? $userId : $request->ip());
        });

        // ── SSO Rate Limiters ─────────────────────────────────────────────────
        // Cegah brute-force dan DoS pada endpoint OAuth2 dan SSO API

        // /oauth/authorize — max 30 req/menit per IP (cegah spam authorization)
        RateLimiter::for('sso-authorize', static function ($request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'error'             => 'too_many_requests',
                        'error_description' => 'Terlalu banyak percobaan otorisasi. Coba lagi dalam satu menit.',
                    ], 429);
                });
        });

        // /oauth/token — max 10 req/menit per IP (cegah brute-force token exchange)
        RateLimiter::for('sso-token', static function ($request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'error'             => 'too_many_requests',
                        'error_description' => 'Terlalu banyak permintaan token. Coba lagi dalam satu menit.',
                    ], 429);
                });
        });

        // /api/user & /api/logout — max 60 req/menit per access token
        RateLimiter::for('sso-api', static function ($request) {
            $tokenId = optional($request->user()?->token())->id ?? $request->ip();
            return Limit::perMinute(60)->by('sso-api|' . $tokenId);
        });

        // ── View Composer ─────────────────────────────────────────────────────
        View::composer('layouts.app', function ($view) {
            $aiOnline = Cache::remember('ai_status', 15, function () {
                try {
                    return Http::timeout(2)->get('http://fastapi-risk:8000/health')->successful();
                } catch (\Exception $e) {
                    return false;
                }
            });

            $view->with('aiOnline', $aiOnline);
        });
    }
}
