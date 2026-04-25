<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WaGateway\Controllers\WaGatewayConfigController;

/*
|--------------------------------------------------------------------------
| WA Gateway Routes (Web)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint', 'role:super-admin,admin,security-officer'])
    ->prefix('dashboard/wa-gateway')
    ->name('wa-gateway.')
    ->group(function () {

        // Dashboard - Halaman utama konfigurasi WA Gateway
        Route::get('/', [WaGatewayConfigController::class, 'index'])
            ->middleware('permission:wa-gateway.view')
            ->name('config.index');

        // Create - Form tambah config
        Route::get('create', [WaGatewayConfigController::class, 'create'])
            ->middleware('permission:wa-gateway.create')
            ->name('config.create');

        // Store - Simpan config baru
        Route::post('/', [WaGatewayConfigController::class, 'store'])
            ->middleware('permission:wa-gateway.create')
            ->name('config.store');

        // System Settings - Konfigurasi global WA module
        Route::get('settings', [WaGatewayConfigController::class, 'systemConfig'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.settings');

        Route::post('settings', [WaGatewayConfigController::class, 'updateSystemConfig'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.settings.update');

        Route::post('settings/test', [WaGatewayConfigController::class, 'testSystemConnection'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.settings.test');

        // Show - Detail config
        Route::get('{config}', [WaGatewayConfigController::class, 'show'])
            ->middleware('permission:wa-gateway.view')
            ->name('config.show');

        // Edit - Form edit config
        Route::get('{config}/edit', [WaGatewayConfigController::class, 'edit'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.edit');

        // Update - Update config
        Route::put('{config}', [WaGatewayConfigController::class, 'update'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.update');

        // Delete - Hapus config
        Route::delete('{config}', [WaGatewayConfigController::class, 'destroy'])
            ->middleware('permission:wa-gateway.delete')
            ->name('config.destroy');

        // Toggle - Aktifkan/nonaktifkan config
        Route::post('{config}/toggle', [WaGatewayConfigController::class, 'toggle'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.toggle');

        // Test - Kirim test message
        Route::post('{config}/test', [WaGatewayConfigController::class, 'testConnection'])
            ->middleware('permission:wa-gateway.update')
            ->name('config.test');

        // Templates - Manajemen template pesan
        Route::post('templates', [WaGatewayConfigController::class, 'storeTemplate'])
            ->middleware('permission:wa-gateway.templates.manage')
            ->name('templates.store');
        Route::put('templates/{template}', [WaGatewayConfigController::class, 'updateTemplate'])
            ->middleware('permission:wa-gateway.templates.manage')
            ->name('templates.update');
        Route::delete('templates/{template}', [WaGatewayConfigController::class, 'destroyTemplate'])
            ->middleware('permission:wa-gateway.templates.manage')
            ->name('templates.destroy');

        // Logs - Ambil logs terbaru untuk dashboard
        Route::get('logs/latest', [WaGatewayConfigController::class, 'getLatestLogs'])
            ->middleware('permission:wa-gateway.view')
            ->name('config.logs.latest');

        // Logs - Ambil logs per gateway
        Route::get('{config}/logs', [WaGatewayConfigController::class, 'getLogs'])
            ->middleware('permission:wa-gateway.view')
            ->name('config.logs');
    });
