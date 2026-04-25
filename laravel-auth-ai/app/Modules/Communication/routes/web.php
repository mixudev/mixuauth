<?php

use App\Modules\Communication\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Communication Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint'])->group(function () {
    
    Route::prefix('dashboard')->group(function () {
        
        // Notifications API & View
        Route::name('dashboard.notifications.')
            ->prefix('notifications')
            ->controller(NotificationController::class)
            ->group(function () {
                // Main View
                Route::get('/', 'all')->name('all');
                
                // API Endpoints
                Route::get('/api', 'index')->name('index');
                Route::post('/api/read-all', 'markAsRead')->name('read-all');
                Route::post('/api/bulk-delete', 'bulkDelete')->name('bulk-delete');
                Route::post('/api/{notification}/read', 'markOneRead')->name('mark-read');
                Route::delete('/api/{notification}', 'delete')->name('delete');
            });
    });
});
