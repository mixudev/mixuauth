<?php

use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Dev\DevMonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
    Route::get('/otp', [WebAuthController::class, 'showOtp'])->name('otp.verify');
    Route::post('/otp', [WebAuthController::class, 'verifyOtp'])->name('otp.verify.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-log', [DashboardController::class, 'auditLog'])->name('audit.log');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
});

Route::prefix('/dev/monitoring')->group(function () {
    // Dashboard & data
    Route::get('/', [DevMonitoringController::class, 'dashboard']);
    Route::get('/api/stats', [DevMonitoringController::class, 'stats']);
    Route::get('/api/otps', [DevMonitoringController::class, 'otps']);
    Route::get('/api/logs', [DevMonitoringController::class, 'loginLogs']);
    Route::get('/api/devices', [DevMonitoringController::class, 'trustedDevices']);
    Route::get('/api/users', [DevMonitoringController::class, 'users']);

    // User block management
    Route::post('/api/users/{userId}/unblock', [DevMonitoringController::class, 'unblockUser']);
    Route::post('/api/users/{userId}/block', [DevMonitoringController::class, 'blockUserManual']);

    // Device management
    Route::post('/api/devices/{deviceId}/revoke', [DevMonitoringController::class, 'revokeDevice']);

    // IP Blacklist
    Route::get('/api/ip-blacklist', [DevMonitoringController::class, 'ipBlacklist']);
    Route::post('/api/ip-blacklist', [DevMonitoringController::class, 'addIpBlacklist']);
    Route::delete('/api/ip-blacklist/{ip}', [DevMonitoringController::class, 'removeIpBlacklist']);

    // IP Whitelist
    Route::get('/api/ip-whitelist', [DevMonitoringController::class, 'ipWhitelist']);
    Route::post('/api/ip-whitelist', [DevMonitoringController::class, 'addIpWhitelist']);
    Route::delete('/api/ip-whitelist/{ip}', [DevMonitoringController::class, 'removeIpWhitelist']);
});