<?php

use App\Modules\Security\Controllers\SecurityController;
use App\Modules\Security\Controllers\SystemHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security Module Routes
|--------------------------------------------------------------------------
|
| Rute untuk pengelolaan sekuritas seperti IP Blacklist/Whitelist, Devices,
| dan pemantauan OTP.
|
*/

Route::middleware([
    'web',
    'auth',
    'ensure.session.version',
    'verify.fingerprint',
    'role:super-admin,admin,security-officer',
])
    ->prefix('admin/security')
    ->name('admin.security.')
    ->group(function () {
        
        // Logs
        Route::get('/logs', [SecurityController::class, 'logs'])
            ->middleware('permission:login-logs.view')
            ->name('logs.index');
        Route::get('/logs/{log}/details', [SecurityController::class, 'logDetails'])
            ->middleware('permission:login-logs.view')
            ->name('logs.details');
        Route::post('/logs/bulk-delete', [SecurityController::class, 'bulkDeleteLogs'])
            ->middleware('permission:login-logs.view')
            ->name('logs.bulk-delete');

        // Devices
        Route::get('/devices', [SecurityController::class, 'devices'])
            ->middleware('permission:trusted-devices.view')
            ->name('devices.index');
        Route::get('/devices/{device}/details', [SecurityController::class, 'deviceDetails'])
            ->middleware('permission:trusted-devices.view')
            ->name('devices.details');
        Route::post('/devices/{device}/revoke', [SecurityController::class, 'revokeDevice'])
            ->middleware('permission:devices.revoke')
            ->name('devices.revoke');

        // OTPs
        Route::get('/otps', [SecurityController::class, 'otps'])
            ->middleware('permission:otp.view')
            ->name('otps.index');

        // Blacklist
        Route::get('/blacklist', [SecurityController::class, 'blacklist'])
            ->middleware('permission:ip-list.view')
            ->name('blacklist.index');
        Route::post('/blacklist', [SecurityController::class, 'storeBlacklist'])
            ->middleware('permission:ip-list.blacklist')
            ->name('blacklist.store');
        Route::delete('/blacklist/{blacklist}', [SecurityController::class, 'destroyBlacklist'])
            ->middleware('permission:ip-list.blacklist')
            ->name('blacklist.destroy');

        // Whitelist
        Route::get('/whitelist', [SecurityController::class, 'whitelist'])
            ->middleware('permission:ip-list.view')
            ->name('whitelist.index');
        Route::post('/whitelist', [SecurityController::class, 'storeWhitelist'])
            ->middleware('permission:ip-list.whitelist')
            ->name('whitelist.store');
        Route::delete('/whitelist/{whitelist}', [SecurityController::class, 'destroyWhitelist'])
            ->middleware('permission:ip-list.whitelist')
            ->name('whitelist.destroy');
    });

// Dashboard API Group
Route::middleware(['web', 'auth', 'ensure.session.version', 'verify.fingerprint'])
    ->prefix('dashboard/api')
    ->name('dashboard.api.')
    ->group(function () {
        Route::get('/system/health', [SystemHealthController::class, 'index'])->name('system.health');
    });
