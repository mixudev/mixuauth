<?php

use App\Modules\Security\Controllers\SecurityController;
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

Route::middleware(['web', 'auth', 'role:super-admin,admin,security-officer'])
    ->prefix('admin/security')
    ->name('admin.security.')
    ->group(function () {
        
        // Logs
        Route::get('/logs', [SecurityController::class, 'logs'])->name('logs.index');

        // Devices
        Route::get('/devices', [SecurityController::class, 'devices'])->name('devices.index');
        Route::post('/devices/{device}/revoke', [SecurityController::class, 'revokeDevice'])->name('devices.revoke');

        // OTPs
        Route::get('/otps', [SecurityController::class, 'otps'])->name('otps.index');

        // Blacklist
        Route::get('/blacklist', [SecurityController::class, 'blacklist'])->name('blacklist.index');
        Route::post('/blacklist', [SecurityController::class, 'storeBlacklist'])->name('blacklist.store');
        Route::delete('/blacklist/{blacklist}', [SecurityController::class, 'destroyBlacklist'])->name('blacklist.destroy');

        // Whitelist
        Route::get('/whitelist', [SecurityController::class, 'whitelist'])->name('whitelist.index');
        Route::post('/whitelist', [SecurityController::class, 'storeWhitelist'])->name('whitelist.store');
        Route::delete('/whitelist/{whitelist}', [SecurityController::class, 'destroyWhitelist'])->name('whitelist.destroy');
    });
