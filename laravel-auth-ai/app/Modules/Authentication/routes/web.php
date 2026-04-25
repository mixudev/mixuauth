<?php

use App\Modules\Authentication\Controllers\WebAuthController;
use App\Modules\Authentication\Controllers\SocialAuthController;
use App\Modules\Authentication\Middleware\PreAuthRateLimitMiddleware;
use App\Modules\Authentication\Middleware\EnsureSessionVersionMiddleware;
use App\Modules\Authentication\Middleware\VerifySessionFingerprintMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web Authentication Module
|--------------------------------------------------------------------------
|
| Semua route terkait autentikasi web (login, logout, MFA, password reset).
| Di-load oleh AuthServiceProvider saat bootstrap aplikasi.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('login.post');

    Route::get('/auth/mfa/verify', [WebAuthController::class, 'showMfaVerify'])->name('auth.mfa.verify');
    Route::post('/auth/mfa/verify', [WebAuthController::class, 'verifyMfa'])
        ->middleware('throttle:mfa')
        ->name('auth.mfa.verify.post');

    // Forgot Password
    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'sendResetLink'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('password.email');
    Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])
        ->middleware(PreAuthRateLimitMiddleware::class)
        ->name('password.update');

    // Social Login - Google
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('/auth/magic-login', [SocialAuthController::class, 'magicLogin'])->name('auth.magic_login');
});
 
Route::middleware([EnsureSessionVersionMiddleware::class, VerifySessionFingerprintMiddleware::class])->group(function () {
    Route::get('/re-login', [WebAuthController::class, 'logout'])
        ->middleware('auth')
        ->name('re-login');
    Route::post('/logout', [WebAuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');
});

