<?php


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Entry Point
|--------------------------------------------------------------------------
|
| File ini hanya sebagai entry point yang me-load routes dari masing-masing
| modul. Logika routing sesungguhnya ada di dalam modul.
|
*/

// Redirect root ke login
Route::get('/', fn() => redirect()->route('login'));

// ── Authentication Module Routes ─────────────────────────────────────────
require app_path('Modules/Authentication/routes/web.php');

// ── Timezone Module Routes ───────────────────────────────────────────────
require app_path('Modules/Timezone/routes/web.php');

// ── Security Module Routes ───────────────────────────────────────────────
require app_path('Modules/Security/routes/web.php');

// ── Identity Module Routes ───────────────────────────────────────────────
require app_path('Modules/Identity/routes/web.php');

// ── Dashboard Module Routes ──────────────────────────────────────────────
require app_path('Modules/Dashboard/routes/web.php');

// Feature Modules (sudah dimodularisasi)

// ── Development Routes (non-production only) ─────────────────────────────
// [H-03 FIX] Route development hanya dimuat di environment yang bukan production.
// Mencegah bocornya dev-tools jika APP_ENV lupa diubah.
if (app()->environment(['local', 'development', 'testing'])) {
    require __DIR__.'/email-test.php';
}
