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
        // ── Shared / Cross-Module Services ────────────────────────────────────
        // Services ini digunakan oleh lebih dari satu modul, sehingga
        // didaftarkan secara global di sini.
        // StatsService dipindahkan ke DashboardServiceProvider
        $this->app->singleton(UserService::class);
    }

    public function boot(): void
    {
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
