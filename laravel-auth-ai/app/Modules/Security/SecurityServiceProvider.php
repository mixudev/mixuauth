<?php

namespace App\Modules\Security;

use App\Modules\Security\Services\AiRiskClientService;
use App\Modules\Security\Services\DeviceFingerprintService;
use App\Modules\Security\Services\GeoIpService;
use App\Modules\Security\Services\RiskFallbackService;
use Illuminate\Support\ServiceProvider;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan layanan-layanan spesifik modul Security.
     * Binding ini memastikan singleton behavior.
     */
    public function register(): void
    {
        // ── Core Security Services (singleton) ─────────
        $this->app->singleton(GeoIpService::class);
        
        $this->app->singleton(DeviceFingerprintService::class, function ($app) {
            return new DeviceFingerprintService(
                $app->make(GeoIpService::class) // Dependency GeoIpService
            );
        });

        $this->app->singleton(AiRiskClientService::class);
        $this->app->singleton(RiskFallbackService::class);
    }

    public function boot(): void
    {
        // Define module-specific policies
        \Illuminate\Support\Facades\Gate::policy(\App\Modules\Security\Models\SecurityNotification::class, \App\Modules\Security\Policies\SecurityNotificationPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Modules\Security\Models\TrustedDevice::class, \App\Modules\Security\Policies\TrustedDevicePolicy::class);

        // Views & Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(resource_path('views/admin/security'), 'security');
    }
}
