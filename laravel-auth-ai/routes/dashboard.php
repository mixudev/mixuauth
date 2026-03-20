<?php

use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Dashboard\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard & Security Routes
| File: routes/web.php (tambahkan route ini)
|--------------------------------------------------------------------------
|
| Semua route dashboard dilindungi middleware auth.
| Jika pakai Spatie Permission, tambahkan ->middleware('role:admin').
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard utama
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Placeholder routes yang direferensikan di Blade
    // Ganti controller sesuai implementasi masing-masing
    Route::get('/security/logs', fn () => view('security.logs'))
        ->name('security.logs');

    Route::get('/security/blacklist', fn () => view('security.blacklist'))
        ->name('security.blacklist');

    Route::get('/security/notifications', fn () => view('security.notifications'))
        ->name('security.notifications');

    Route::prefix('dashboard/users')->name('dashboard.users.')->group(function () {

        // Index (halaman + list)
        Route::get('/', [UserManagementController::class, 'index'])->name('index');

        // CRUD
        Route::post('/',            [UserManagementController::class, 'store'])->name('store');
        Route::put('/{user}',       [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}',    [UserManagementController::class, 'destroy'])->name('destroy');

        // Block / Unblock
        Route::post('/{user}/block',   [UserManagementController::class, 'block'])->name('block');
        Route::post('/{user}/unblock', [UserManagementController::class, 'unblock'])->name('unblock');

        // Reset password
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');

        // Bulk actions
        Route::post('/bulk', [UserManagementController::class, 'bulkAction'])->name('bulk');
    });

});
