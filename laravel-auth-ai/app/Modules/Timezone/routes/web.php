<?php

use App\Modules\Timezone\Controllers\TimezoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Timezone Module Routes
|--------------------------------------------------------------------------
|
| Rute untuk sinkronisasi dan penyimpanan timezone pengguna.
|
*/

Route::post('/timezone/set', [TimezoneController::class, 'set'])
    ->middleware(['web', 'throttle:10,1'])
    ->name('timezone.set');

Route::patch('/timezone/update', [TimezoneController::class, 'update'])
    ->middleware(['web', 'auth', 'throttle:10,1'])
    ->name('timezone.update');
