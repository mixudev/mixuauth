<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint', 'role:super-admin,admin,security-officer'])
    ->prefix('dashboard')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard Home (Overview & Stats)
        |--------------------------------------------------------------------------
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

    });
