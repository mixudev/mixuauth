<?php

/**
 * ════════════════════════════════════════════════════════════════════
 *  EMAIL TEST ROUTES — DATA DUMMY
 *  File: routes/web.php  (atau buat file routes/email-test.php)
 * ════════════════════════════════════════════════════════════════════
 *
 *  CARA PAKAI:
 *  1. Pastikan MAIL_MAILER=log di .env (log ke storage/logs/laravel.log)
 *     atau gunakan Mailtrap / Mailpit untuk preview visual.
 *
 *  2. Tambahkan route ini ke routes/web.php:
 *     require __DIR__ . '/email-test.php';
 *
 *  3. Akses di browser (hanya aktif di environment local):
 *     GET /email-test/verify         → kirim & preview verify email
 *     GET /email-test/reset-password → kirim & preview reset password
 *     GET /email-test/welcome        → kirim & preview welcome email
 *     GET /email-test/preview/{type} → preview HTML tanpa kirim
 *
 * ════════════════════════════════════════════════════════════════════
 */

use App\Mail\Auth\ResetPasswordEmail;
use App\Mail\Identity\VerifyEmail;
use App\Mail\Identity\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// ── Guard: hanya jalan di local/development ──────────────────────────
if (! app()->environment(['local', 'development'])) {
    return;
}

// ── Dummy Data ───────────────────────────────────────────────────────
$dummy = [
    'userName'     => 'Budi Santoso',
    'userEmail'    => 'budi.santoso@example.com',
    'plan'         => 'Professional',
    'createdAt'    => now()->translatedFormat('d F Y'),
    'ipAddress'    => '118.99.44.12',
    'expiresIn'    => '24 jam',

    // URL dummy — ganti dengan URL asli saat production
    'verifyUrl'    => 'https://yourapp.test/email/verify/1/abc123?expires=1234567890&signature=xyz',
    'resetUrl'     => 'https://yourapp.test/reset-password/abc123def456?email=budi%40example.com',
    'loginUrl'     => 'https://yourapp.test/login',

    // Unsubscribe URL
    'unsubUrl'     => 'https://yourapp.test/unsubscribe?token=abc123',
];

// ════════════════════════════════════════════════════════════════════
//  1. VERIFY EMAIL — kirim via mailer yang dikonfigurasi
// ════════════════════════════════════════════════════════════════════
Route::get('/email-test/verify', function () use ($dummy) {

    $mailable = new VerifyEmail(
        userName:       $dummy['userName'],
        userEmail:      $dummy['userEmail'],
        actionUrl:      $dummy['verifyUrl'],
        expiresIn:      $dummy['expiresIn'],
        unsubscribeUrl: $dummy['unsubUrl'],
    );

    Mail::to($dummy['userEmail'])->send($mailable);

    return response()->json([
        'status'  => 'sent',
        'type'    => 'verify',
        'to'      => $dummy['userEmail'],
        'mailer'  => config('mail.default'),
        'note'    => config('mail.default') === 'log'
            ? 'Cek storage/logs/laravel.log'
            : 'Cek inbox ' . $dummy['userEmail'],
    ]);

})->name('email-test.verify');


// ════════════════════════════════════════════════════════════════════
//  2. RESET PASSWORD — kirim via mailer yang dikonfigurasi
// ════════════════════════════════════════════════════════════════════
Route::get('/email-test/reset-password', function () use ($dummy) {

    $mailable = new ResetPasswordEmail(
        userName:       $dummy['userName'],
        userEmail:      $dummy['userEmail'],
        actionUrl:      $dummy['resetUrl'],
        expiresIn:      '60 menit',
        ipAddress:      $dummy['ipAddress'],
        unsubscribeUrl: $dummy['unsubUrl'],
    );

    Mail::to($dummy['userEmail'])->send($mailable);

    return response()->json([
        'status'  => 'sent',
        'type'    => 'reset-password',
        'to'      => $dummy['userEmail'],
        'mailer'  => config('mail.default'),
        'note'    => config('mail.default') === 'log'
            ? 'Cek storage/logs/laravel.log'
            : 'Cek inbox ' . $dummy['userEmail'],
    ]);

})->name('email-test.reset-password');


// ════════════════════════════════════════════════════════════════════
//  3. WELCOME — kirim via mailer yang dikonfigurasi
// ════════════════════════════════════════════════════════════════════
Route::get('/email-test/welcome', function () use ($dummy) {

    $mailable = new WelcomeEmail(
        userName:       $dummy['userName'],
        userEmail:      $dummy['userEmail'],
        loginUrl:       $dummy['loginUrl'],
        // plan:           $dummy['plan'],
        createdAt:      $dummy['createdAt'],
        unsubscribeUrl: $dummy['unsubUrl'],
    );

    Mail::to($dummy['userEmail'])->send($mailable);

    return response()->json([
        'status'  => 'sent',
        'type'    => 'welcome',
        'to'      => $dummy['userEmail'],
        'mailer'  => config('mail.default'),
        'note'    => config('mail.default') === 'log'
            ? 'Cek storage/logs/laravel.log'
            : 'Cek inbox ' . $dummy['userEmail'],
    ]);

})->name('email-test.welcome');


// ════════════════════════════════════════════════════════════════════
//  4. PREVIEW — render HTML langsung di browser (tanpa kirim)
//     GET /email-test/preview/verify
//     GET /email-test/preview/reset-password
//     GET /email-test/preview/welcome
// ════════════════════════════════════════════════════════════════════
Route::get('/email-test/preview/{type}', function (string $type) use ($dummy) {

    $mailable = match ($type) {

        'verify' => new VerifyEmail(
            userName:       $dummy['userName'],
            userEmail:      $dummy['userEmail'],
            actionUrl:      $dummy['verifyUrl'],
            expiresIn:      $dummy['expiresIn'],
            unsubscribeUrl: $dummy['unsubUrl'],
        ),

        'reset-password' => new ResetPasswordEmail(
            userName:       $dummy['userName'],
            userEmail:      $dummy['userEmail'],
            actionUrl:      $dummy['resetUrl'],
            expiresIn:      '60 menit',
            ipAddress:      $dummy['ipAddress'],
            unsubscribeUrl: $dummy['unsubUrl'],
        ),

        'welcome' => new WelcomeEmail(
            userName:       $dummy['userName'],
            userEmail:      $dummy['userEmail'],
            loginUrl:       $dummy['loginUrl'],
            createdAt:      $dummy['createdAt'],
            unsubscribeUrl: $dummy['unsubUrl'],
        ),

        default => abort(404, "Tipe email '{$type}' tidak ditemukan. Pilihan: verify, reset-password, welcome"),
    };

    // Render HTML langsung ke browser
    return $mailable->render();

})->name('email-test.preview')->where('type', '[a-z\-]+');


// ════════════════════════════════════════════════════════════════════
//  5. INDEX — daftar semua route testing
// ════════════════════════════════════════════════════════════════════
Route::get('/email-test', function () {
    $base    = url('/email-test');
    $routes  = [
        'KIRIM'   => [
            ['method' => 'GET', 'url' => "{$base}/verify",         'desc' => 'Kirim Verify Email'],
            ['method' => 'GET', 'url' => "{$base}/reset-password", 'desc' => 'Kirim Reset Password Email'],
            ['method' => 'GET', 'url' => "{$base}/welcome",        'desc' => 'Kirim Welcome Email'],
        ],
        'PREVIEW' => [
            ['method' => 'GET', 'url' => "{$base}/preview/verify",         'desc' => 'Preview Verify Email (browser)'],
            ['method' => 'GET', 'url' => "{$base}/preview/reset-password", 'desc' => 'Preview Reset Password (browser)'],
            ['method' => 'GET', 'url' => "{$base}/preview/welcome",        'desc' => 'Preview Welcome Email (browser)'],
        ],
    ];

    return response()->json([
        'app'         => config('app.name'),
        'environment' => app()->environment(),
        'mailer'      => config('mail.default'),
        'from'        => config('mail.from'),
        'routes'      => $routes,
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

})->name('email-test.index');
