<?php

use App\Modules\SSO\Controllers\UserInfoController;
use App\Modules\SSO\Controllers\SsoLogoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SSO Server — API Routes
|--------------------------------------------------------------------------
|
| Endpoint-endpoint ini dilindungi via Laravel Passport Bearer Token.
| Guard 'api' dikonfigurasi menggunakan driver 'passport' di config/auth.php.
|
| Client (mixu/sso-auth) memanggil:
|   GET  /api/user    → profil user + roles + access_areas
|   POST /api/logout  → logout + trigger global logout webhook ke semua client
|
*/

Route::middleware(['auth:api', 'throttle:sso-api', \App\Http\Middleware\SsoSecurityHeadersMiddleware::class])->group(function () {

    // Profil user yang terautentikasi
    Route::get('/user', [UserInfoController::class, 'show']);

    // Logout dari SSO Server (revoke token + webhook global logout)
    Route::post('/logout', [SsoLogoutController::class, 'handle']);
});
