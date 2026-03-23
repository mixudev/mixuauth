<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dashboard\UserManagementController;
use App\Http\Controllers\Admin\Dashboard\NotificationController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])
    ->prefix('dashboard')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard Home
        |--------------------------------------------------------------------------
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Dashboard - User Management
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.users.')
            ->prefix('users')
            ->controller(UserManagementController::class)
            ->group(function () {

                // Index
                Route::get('/', 'index')->name('index');

                // CRUD
                Route::post('/', 'store')->name('store');
                Route::put('/{user}', 'update')->name('update');
                Route::delete('/{user}', 'destroy')->name('destroy');

                // Account Controls
                Route::post('/{user}/block', 'block')->name('block');
                Route::post('/{user}/unblock', 'unblock')->name('unblock');
                Route::post('/{user}/reset-password', 'resetPassword')
                    ->name('reset-password');

                // Bulk
                Route::post('/bulk', 'bulkAction')->name('bulk');
            });

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Notifications API
        |--------------------------------------------------------------------------
        */
        Route::name('dashboard.notifications.')
            ->prefix('api/notifications')
            ->controller(NotificationController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/read-all', 'markAsRead')->name('read-all');
                Route::post('/{notification}/read', 'markOneRead')->name('mark-read');
                Route::delete('/{notification}', 'delete')->name('delete');
            });

        Route::get('/notifications', [NotificationController::class, 'all'])
            ->name('dashboard.notifications.all');


    });