<?php

namespace App\Modules\Identity;

use App\Modules\Identity\Services\UserService;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    /**
     * Mendaftarkan layanan Identity.
     */
    public function register(): void
    {
        $this->app->singleton(UserService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(resource_path('views/admin/identity'), 'identity');
    }
}
