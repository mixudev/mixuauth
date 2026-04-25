<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

/*
|--------------------------------------------------------------------------
| API Routes — Entry Point
|--------------------------------------------------------------------------
|
| File ini hanya sebagai entry point yang me-load routes dari masing-masing
| modul. Logika routing sesungguhnya ada di dalam modul.
|
*/

// ── Authentication Module API Routes ────────────────────────────────────
require app_path('Modules/Authentication/routes/api.php');

// WhatsApp Gateway Routes — dilindungi auth:sanctum + throttle + permission
Route::middleware(['auth:sanctum', 'throttle:wa-send'])->group(function () {
    Route::post('/whatsapp/send', [WhatsAppController::class, 'send'])
        ->middleware('permission:wa-gateway.send');
});


