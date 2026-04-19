<?php

namespace App\Modules\Authentication;

use App\Modules\Authentication\Middleware\EnsureSessionVersionMiddleware;
use App\Modules\Authentication\Middleware\PreAuthRateLimitMiddleware;
use App\Modules\Authentication\Middleware\VerifySessionFingerprintMiddleware;
use App\Modules\Authentication\Services\AuthFlowService;
use App\Modules\Authentication\Services\BlockingService;
use App\Modules\Authentication\Services\LoginAuditService;
use App\Modules\Authentication\Services\LoginRiskService;
use App\Modules\Authentication\Services\Mfa\MfaManager;
use App\Modules\Authentication\Services\Mfa\Strategies\EmailMfaStrategy;
use App\Modules\Authentication\Services\Mfa\Strategies\TotpMfaStrategy;
use App\Modules\Authentication\Services\OtpService;
use App\Modules\Authentication\Services\PasswordResetService;
use App\Modules\Security\Repositories\TrustedDeviceRepository;
use App\Modules\Security\Services\AiRiskClientService;
use App\Modules\Security\Services\DeviceFingerprintService;
use App\Modules\Security\Services\RiskFallbackService;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Authentication Module Service Provider
    |
    | Mendaftarkan semua binding, singleton, dan middleware yang dibutuhkan
    | oleh modul autentikasi. Diload via bootstrap/app.php withProviders().
    |--------------------------------------------------------------------------
    */

    public function register(): void
    {
        // ── Core Security Services (singleton per request lifecycle) ─────────
        $this->app->singleton(DeviceFingerprintService::class);
        $this->app->singleton(AiRiskClientService::class);
        $this->app->singleton(RiskFallbackService::class);
        $this->app->singleton(OtpService::class);
        $this->app->singleton(BlockingService::class);
        $this->app->singleton(PasswordResetService::class);

        // ── Services dengan dependency injection ─────────────────────────────
        $this->app->singleton(LoginRiskService::class, function ($app) {
            return new LoginRiskService(
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(LoginAuditService::class, function ($app) {
            return new LoginAuditService(
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(TrustedDeviceRepository::class, function ($app) {
            return new TrustedDeviceRepository(
                $app->make(DeviceFingerprintService::class)
            );
        });

        // ── MFA Stack ────────────────────────────────────────────────────────
        $this->app->singleton(EmailMfaStrategy::class, function ($app) {
            return new EmailMfaStrategy(
                $app->make(OtpService::class),
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(TotpMfaStrategy::class, function ($app) {
            return new TotpMfaStrategy(
                $app->make(DeviceFingerprintService::class)
            );
        });

        $this->app->singleton(MfaManager::class, function ($app) {
            return new MfaManager(
                $app->make(EmailMfaStrategy::class),
                $app->make(TotpMfaStrategy::class)
            );
        });

        // ── Auth Flow Orchestrator ────────────────────────────────────────────
        $this->app->singleton(AuthFlowService::class, function ($app) {
            return new AuthFlowService(
                $app->make(LoginRiskService::class),
                $app->make(AiRiskClientService::class),
                $app->make(RiskFallbackService::class),
                $app->make(LoginAuditService::class),
                $app->make(TrustedDeviceRepository::class),
                $app->make(DeviceFingerprintService::class),
                $app->make(BlockingService::class),
                $app->make(PasswordResetService::class),
                $app->make(MfaManager::class),
            );
        });
    }

    public function boot(): void
    {
        // Routes module di-load via bootstrap/app.php withRouting()
        // atau dapat di-load di sini secara manual:
        //
        // $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        // $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }
}
