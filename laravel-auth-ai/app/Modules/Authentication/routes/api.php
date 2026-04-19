<?php

use App\Modules\Authentication\Controllers\AuthController;
use App\Modules\Authentication\Middleware\PreAuthRateLimitMiddleware;
use App\Modules\Authentication\Middleware\VerifySessionFingerprintMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API Authentication Module
|--------------------------------------------------------------------------
|
| Semua route API terkait autentikasi (stateless JSON responses).
| Di-load oleh AuthServiceProvider saat bootstrap aplikasi.
|
*/

// -- Route publik: tidak memerlukan autentikasi
Route::prefix('auth')->name('api.auth.')->group(function () {

    // Login dengan penilaian risiko AI
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('login');

    // Verifikasi MFA (dipanggil setelah keputusan MFA dari sistem)
    Route::post('/mfa/verify', [AuthController::class, 'verifyMfa'])
        ->middleware(['throttle:mfa'])
        ->name('mfa.verify');

    // Password Reset API
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('password.email');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('password.update');

    Route::get('/reset-password/validate', [AuthController::class, 'validateResetToken'])
        ->name('password.validate');
});

// -- Route yang memerlukan autentikasi
Route::prefix('auth')->name('api.auth.')->middleware([
    'auth',
    'ensure.session.version',
    VerifySessionFingerprintMiddleware::class,
])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
