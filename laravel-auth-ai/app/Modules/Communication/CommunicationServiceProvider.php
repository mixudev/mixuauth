<?php

namespace App\Modules\Communication;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViewsFrom(resource_path('views/admin/communication'), 'communication');
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
    }

    private function loadRoutes(): void
    {
        if (file_exists(__DIR__ . '/routes/web.php')) {
            Route::middleware('web')
                ->group(__DIR__ . '/routes/web.php');
        }

        if (file_exists(__DIR__ . '/routes/api.php')) {
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__ . '/routes/api.php');
        }
    }
}
