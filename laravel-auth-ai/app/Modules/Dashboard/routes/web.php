<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Controllers\DashboardController;
use App\Modules\Dashboard\Controllers\GuestPortalController;

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

/*
|--------------------------------------------------------------------------
| Guest Portal — untuk user login dengan role tapi tanpa akses dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint'])
    ->get('/guest-portal', [GuestPortalController::class, 'index'])
    ->name('guest.portal');
