<?php

namespace App\Modules\Dashboard;

use App\Modules\Dashboard\Services\DashboardStatsService;
use App\Modules\Dashboard\Services\DashboardChartService;
use App\Modules\Dashboard\Services\StatsService;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DashboardStatsService::class);
        $this->app->singleton(DashboardChartService::class);
        $this->app->singleton(StatsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(\App\Modules\Dashboard\Services\StatsService $statscount): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(resource_path('views/admin/dashboard'), 'dashboard');
        
        \Illuminate\Support\Facades\View::composer([
            'admin.*', 
            'dashboard::*', 
            'identity::*', 
            'authorization::*', 
            'communication::*',
            'security::*',
            'partials.*',
            'layouts.*'
        ], function ($view) use ($statscount) {
            $view->with('statscount', $statscount->get());
        });
    }
}
