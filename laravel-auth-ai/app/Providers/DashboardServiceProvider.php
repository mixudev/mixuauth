<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Services\Stats\StatsService;

class DashboardServiceProvider extends ServiceProvider
{
    public function boot(StatsService $stats): void
    {
        View::composer('admin.*', function ($view) use ($stats) {
            $view->with('stats', $stats->get());
        });
    }
}