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

Route::middleware(['auth'])
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

        /*
        |--------------------------------------------------------------------------
        | Dashboard - Profile
        |--------------------------------------------------------------------------
        */
        Route::controller(\App\Http\Controllers\Admin\Dashboard\ProfileController::class)
            ->prefix('profile')
            ->name('dashboard.profile.')
            ->group(function () {
                // Satu route untuk semua panel (panel via ?panel=xxx)
                Route::get('/', 'show')->name('show');

                // Aksi form
                Route::post('/update', 'update')->name('update');
                Route::post('/password', 'updatePassword')->name('password');
                Route::post('/password/reset', 'requestPasswordReset')->name('password.reset_request');
                Route::post('/preferences', 'updatePreferences')->name('preferences.update');
                Route::delete('/devices/{device}', 'revokeDevice')->name('devices.revoke');

                // MFA (JSON API)
                Route::get('/mfa/setup', 'setupMfa')->name('mfa.setup');
                Route::post('/mfa/confirm', 'confirmMfa')->name('mfa.confirm');
                Route::post('/mfa/disable', 'disableMfa')->name('mfa.disable');
            });
    });