<?php

use App\Http\Controllers\Web\WebAuthController;
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

    Route::resource('users', \App\Http\Controllers\Web\UserController::class);
    
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('dashboard');

});

// panggil route scurity (dev monitoring)
require __DIR__.'/scurity.php';