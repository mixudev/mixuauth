<?php

use App\Modules\Identity\Controllers\EmailVerificationController;
use App\Modules\Identity\Controllers\UserManagementController;
use App\Modules\Identity\Controllers\ProfileController;
use App\Modules\Identity\Controllers\GlobalSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Identity Module Routes
|--------------------------------------------------------------------------
|
| Rute untuk profil pengguna dan verifikasi identitas (email).
|
*/

Route::get('/verify-email', function () {
    return redirect()->route('dashboard');
})->name('verification.notice');

Route::get('/verify-email/{uuid}/{token}', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('verification.verify');

Route::get('/email/verified', [EmailVerificationController::class, 'verified'])
    ->name('verification.verified')
    ->middleware('signed');

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint'])->group(function () {
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:verification-send')
        ->name('verification.send');

    Route::post('/email/verification-notification/{id}', [EmailVerificationController::class, 'sendToUser'])
        ->middleware(['permission:users.edit', 'throttle:verification-send'])
        ->name('verification.send.admin');

    // ─── Dashboard Integration ─────────────────────────────────────────────
    
    Route::prefix('dashboard')->group(function () {
        
        // Global Search API
        Route::get('/api/global-search', [GlobalSearchController::class, 'search'])
            ->name('dashboard.api.search');

        // User Management
        Route::name('dashboard.users.')
            ->prefix('users')
            ->controller(UserManagementController::class)
            ->middleware(['role:super-admin,admin', 'permission:users.view'])
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store')->middleware('permission:users.create');
                Route::put('/{user}', 'update')->name('update')->middleware('permission:users.edit');
                Route::delete('/{user}', 'destroy')->name('destroy')->middleware('permission:users.delete');
                Route::post('/{user}/block', 'block')->name('block')->middleware('permission:users.edit');
                Route::post('/{user}/unblock', 'unblock')->name('unblock')->middleware('permission:users.edit');
                Route::post('/{user}/reset-password', 'resetPassword')->name('reset-password')->middleware('permission:users.edit');
                Route::post('/bulk', 'bulkAction')->name('bulk')->middleware('permission:users.edit');
            });

        // Profile Management
        Route::controller(ProfileController::class)
            ->prefix('profile')
            ->name('dashboard.profile.')
            ->group(function () {
                Route::get('/', 'show')->name('show');
                Route::post('/update', 'update')->name('update');
                Route::post('/password', 'updatePassword')->name('password');
                Route::post('/password/reset', 'requestPasswordReset')->name('password.reset_request');
                Route::post('/preferences', 'updatePreferences')->name('preferences.update');
                Route::delete('/devices/{device}', 'revokeDevice')->name('devices.revoke');
                Route::get('/mfa/setup', 'setupMfa')->name('mfa.setup');
                Route::post('/mfa/confirm', 'confirmMfa')->name('mfa.confirm');
                Route::post('/mfa/disable', 'disableMfa')->name('mfa.disable');
            });
    });
});
