<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Riders\RiderController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// --- RUTAS DE VISTA PARA RIDERS ---
Route::get('/riders', [RiderController::class, 'index'])->name('admin.riders.index');
Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('admin.riders.show');

// --- ACCOUNTS & ASSIGNMENTS ---
Route::apiResource('accounts', \App\Http\Controllers\Admin\Accounts\AccountController::class)
    ->middleware('throttle:60,1');

Route::get('accounts/{account}/assignments', [\App\Http\Controllers\Admin\Accounts\AccountAssignmentController::class, 'index'])
    ->name('admin.accounts.assignments.index');

Route::post('accounts/{account}/assignments', [\App\Http\Controllers\Admin\Accounts\AccountAssignmentController::class, 'store'])
    ->name('admin.accounts.assignments.store')
    ->middleware('role:super-admin|zone-manager', 'throttle:30,1');

Route::post('assignments/{assignment}/end', [\App\Http\Controllers\Admin\Accounts\AccountAssignmentController::class, 'end'])
    ->name('admin.assignments.end')
    ->middleware('role:super-admin|zone-manager', 'throttle:30,1');

// --- RUTAS DE API PARA RIDERS ---
Route::prefix('api')->name('admin.api.')->group(function () {
    Route::get('/riders', [RiderController::class, 'list'])->name('riders.list');
    Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');
    Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('riders.show'); // Esta ruta es usada por el JS
    Route::put('/riders/{rider}', [RiderController::class, 'update'])->name('riders.update');
    Route::delete('/riders/{rider}', [RiderController::class, 'destroy'])->name('riders.destroy');
});
