<?php

use App\Http\Controllers\Admin\Security\DevMonitoringController;
use App\Http\Controllers\Admin\Security\DevStatsController;
use App\Http\Controllers\Admin\Security\DevOtpController;
use App\Http\Controllers\Admin\Security\DevLoginLogController;
use App\Http\Controllers\Admin\Security\DevTrustedDeviceController;
use App\Http\Controllers\Admin\Security\DevUserController;
use App\Http\Controllers\Admin\Security\DevIpBlacklistController;
use App\Http\Controllers\Admin\Security\DevIpWhitelistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DEV ONLY — Auth Monitoring Routes
|--------------------------------------------------------------------------
|
| REMOVE or gate behind middleware (e.g. `dev.only`, `ip.whitelist`)
| before deploying to any shared / production environment.
|
| Recommended: wrap with ->middleware(['auth', 'role:super-admin'])
|              or restrict by IP at the nginx/Apache level.
|
*/

Route::prefix('/dev/monitoring')->name('dev.monitoring.')->group(function () {

    // ── Dashboard (SPA shell) ────────────────────────────────────────────
    Route::get('/', [DevMonitoringController::class, 'dashboard'])->name('dashboard');

    // ── Stats ────────────────────────────────────────────────────────────
    Route::get('/api/stats', DevStatsController::class)->name('api.stats');

    // ── OTP Verifications ────────────────────────────────────────────────
    Route::get('/api/otps', [DevOtpController::class, 'index'])->name('api.otps.index');

    // ── Login Logs ───────────────────────────────────────────────────────
    Route::get('/api/logs',          [DevLoginLogController::class, 'index'])->name('api.logs.index');
    Route::get('/api/export/logs',   [DevLoginLogController::class, 'export'])->name('api.logs.export');

    // ── Trusted Devices ──────────────────────────────────────────────────
    Route::get('/api/devices',                [DevTrustedDeviceController::class, 'index'])->name('api.devices.index');
    Route::post('/api/devices/{deviceId}/revoke', [DevTrustedDeviceController::class, 'revoke'])->name('api.devices.revoke');

    // ── Users ────────────────────────────────────────────────────────────
    Route::get('/api/users',                  [DevUserController::class, 'index'])->name('api.users.index');
    Route::post('/api/users/{userId}/unblock',[DevUserController::class, 'unblock'])->name('api.users.unblock');
    Route::post('/api/users/{userId}/block',  [DevUserController::class, 'block'])->name('api.users.block');

    // ── IP Blacklist ─────────────────────────────────────────────────────
    Route::get('/api/ip-blacklist',        [DevIpBlacklistController::class, 'index'])->name('api.blacklist.index');
    Route::post('/api/ip-blacklist',       [DevIpBlacklistController::class, 'store'])->name('api.blacklist.store');
    Route::delete('/api/ip-blacklist/{ip}',[DevIpBlacklistController::class, 'destroy'])->name('api.blacklist.destroy');

    // ── IP Whitelist ─────────────────────────────────────────────────────
    Route::get('/api/ip-whitelist',        [DevIpWhitelistController::class, 'index'])->name('api.whitelist.index');
    Route::post('/api/ip-whitelist',       [DevIpWhitelistController::class, 'store'])->name('api.whitelist.store');
    Route::delete('/api/ip-whitelist/{ip}',[DevIpWhitelistController::class, 'destroy'])->name('api.whitelist.destroy');
});

/*
|--------------------------------------------------------------------------
| Security Pages (View Only / Placeholder)
|--------------------------------------------------------------------------
*/
Route::prefix('security')->name('security.')->group(function () {

    Route::get('/logs', fn () => view('security.logs'))
        ->name('logs');

    Route::get('/blacklist', fn () => view('security.blacklist'))
        ->name('blacklist');

    Route::get('/notifications', fn () => view('security.notifications'))
        ->name('notifications');

});
