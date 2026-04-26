<?php

use App\Modules\SSO\Controllers\OAuthController;
use App\Modules\SSO\Controllers\Admin\SsoClientController;
use App\Modules\SSO\Controllers\Admin\AccessAreaController;
use App\Modules\SSO\Controllers\Admin\ApplicationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SSO Server — Web Routes
|--------------------------------------------------------------------------
|
| 1. /oauth/authorize  — Consent page (user harus sudah login di sistem kita)
| 2. /oauth/token      — Exchange code for access token
| 3. /dashboard/sso/*  — Admin UI untuk manage SSO clients & access areas
|
*/

// Passport Token Routes (Manual Registration)
Route::group([
    'as'        => 'passport.',
    'prefix'    => 'oauth',
    'namespace' => 'Laravel\Passport\Http\Controllers',
    'middleware' => ['throttle:sso-token', App\Http\Middleware\SsoSecurityHeadersMiddleware::class],
], function () {
    Route::post('/token', [
        'uses' => 'AccessTokenController@issueToken',
        'as'   => 'token',
    ]);
});


/*
|--------------------------------------------------------------------------
| OAuth Consent Page
|--------------------------------------------------------------------------
| Middleware 'auth' memastikan user sudah login via sistem kita.
| Jika belum login, OAuthController@show akan redirect ke login page.
*/

Route::middleware(['web'])->group(function () {
    Route::get('/oauth/authorize', [OAuthController::class, 'show'])
        ->name('passport.authorizations.authorize')
        ->middleware('throttle:sso-authorize');  // Rate limit: 30 req/menit per IP

    Route::post('/oauth/authorize', [OAuthController::class, 'approve'])
        ->name('passport.authorizations.approve')
        ->middleware('auth');

    Route::delete('/oauth/authorize', [OAuthController::class, 'deny'])
        ->name('passport.authorizations.deny')
        ->middleware('auth');

    // Halaman akses ditolak (area mismatch / client inactive)
    Route::get('/sso/access-denied', function () {
        return view('sso.access-denied');
    })->name('sso.access-denied');
});


/*
|--------------------------------------------------------------------------
| SSO Admin Panel (Super-Admin Only)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'ensure.session.version',
    'verify.fingerprint',
    'role:super-admin',
])
->prefix('dashboard/sso')
->name('sso.')
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | SSO Client Apps Management
    |--------------------------------------------------------------------------
    */
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{client}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::resource('clients', SsoClientController::class)->except(['create', 'show', 'edit']);

    // Regenerate secrets (OAuth & Webhook)
    Route::post('clients/{client}/generate-token', [SsoClientController::class, 'generateToken'])
        ->name('clients.generate-token');

    // Test Webhook Connection
    Route::post('clients/{client}/test-webhook', [SsoClientController::class, 'testWebhook'])
        ->name('clients.test-webhook');

    // ── Access Area Management for Clients ──────────────────────────────────
    Route::get('clients/{client}/access-areas', [SsoClientController::class, 'editAccessAreas'])
        ->name('clients.edit-access-areas');
    Route::post('clients/{client}/access-areas', [SsoClientController::class, 'syncAccessAreas'])
        ->name('clients.sync-access-areas');


    /*
    |--------------------------------------------------------------------------
    | Access Areas Management
    |--------------------------------------------------------------------------
    */
    Route::resource('access-areas', AccessAreaController::class)->except(['create']);

    // Assign/Revoke access areas to/from users
    Route::post('access-areas/{access_area}/assign-users', [AccessAreaController::class, 'assignToUser'])
        ->name('access-areas.assign-user');
    
    Route::post('access-areas/{access_area}/revoke-users', [AccessAreaController::class, 'revokeUsers'])
        ->name('access-areas.revoke-user');
});
