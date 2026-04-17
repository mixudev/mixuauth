<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\PreAuthRateLimitMiddleware;
use App\Http\Middleware\VerifySessionFingerprintMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Autentikasi API
|--------------------------------------------------------------------------
|
| Semua route login menggunakan PreAuthRateLimitMiddleware yang berjalan
| sebelum controller dieksekusi, memastikan rate limiting terjadi bahkan
| sebelum ada akses ke database.
|
*/

// -- Route publik: tidak memerlukan autentikasi
Route::prefix('auth')->name('auth.')->group(function () {

    // Login dengan penilaian risiko AI
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('login');

    // Verifikasi MFA (dipanggil setelah keputusan MFA dari sistem)
    Route::post('/mfa/verify', [AuthController::class, 'verifyMfa'])
        ->middleware(PreAuthRateLimitMiddleware::class)
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
Route::prefix('auth')->name('auth.')->middleware([
    'auth:sanctum',
    VerifySessionFingerprintMiddleware::class,
])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

