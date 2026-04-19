<?php

use App\Modules\Authorization\Controllers\RoleManagementController;
use App\Modules\Authorization\Controllers\PermissionManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authorization Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'ensure.session.version', 'verify.fingerprint', 'role:admin|superadmin'])->group(function () {
    
    Route::prefix('dashboard')->group(function () {
        
        // Role Management
        Route::name('dashboard.roles.')
            ->prefix('roles')
            ->controller(RoleManagementController::class)
            ->middleware('permission:roles.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create')->middleware('permission:roles.create');
                Route::post('/', 'store')->name('store')->middleware('permission:roles.create');
                Route::get('/{role}/edit', 'edit')->name('edit')->middleware('permission:roles.edit');
                Route::put('/{role}', 'update')->name('update')->middleware('permission:roles.edit');
                Route::delete('/{role}', 'destroy')->name('destroy')->middleware('permission:roles.delete');
                Route::get('/api/permissions', 'getPermissions')->name('api.permissions');
            });

        // Permission Management
        Route::name('dashboard.permissions.')
            ->prefix('permissions')
            ->controller(PermissionManagementController::class)
            ->middleware('permission:permissions.view')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create')->middleware('permission:permissions.create');
                Route::post('/', 'store')->name('store')->middleware('permission:permissions.create');
                Route::get('/{permission}/edit', 'edit')->name('edit')->middleware('permission:permissions.edit');
                Route::put('/{permission}', 'update')->name('update')->middleware('permission:permissions.edit');
                Route::delete('/{permission}', 'destroy')->name('destroy')->middleware('permission:permissions.delete');
            });
    });
});
