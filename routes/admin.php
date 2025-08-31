<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Riders\RiderController;
use App\Http\Controllers\Admin\Accounts\AccountController;
use App\Http\Controllers\Admin\Accounts\AccountAssignmentController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Todas las rutas aquí ya tienen el prefijo '/admin' por defecto.
*/

// --- DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');


// --- RUTAS DE VISTAS (devuelven HTML) ---
Route::get('/riders', [RiderController::class, 'index'])->name('admin.riders.index');
Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('admin.riders.show');

Route::get('/accounts', [AccountController::class, 'index'])->name('admin.accounts.index');
Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('admin.accounts.show');


// --- RUTAS DE API (devuelven JSON) ---
Route::prefix('api')->name('admin.api.')->group(function () {

    // Endpoints para Riders
    Route::get('/riders', [RiderController::class, 'list'])->name('riders.list');
    Route::get('/riders/active-list', [RiderController::class, 'getActiveRiders'])->name('riders.active-list');
    Route::post('/riders', [RiderController::class, 'store'])->name('riders.store');
    Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('riders.show');
    Route::put('/riders/{rider}', [RiderController::class, 'update'])->name('riders.update');
    Route::delete('/riders/{rider}', [RiderController::class, 'destroy'])->name('riders.destroy');

    // Endpoints para Accounts
    Route::get('/accounts', [AccountController::class, 'list'])->name('accounts.list');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

    // Endpoints para Assignments
    Route::get('/accounts/{account}/assignments', [AccountAssignmentController::class, 'index'])->name('accounts.assignments.index');
    Route::post('/accounts/{account}/assignments', [AccountAssignmentController::class, 'store'])->name('accounts.assignments.store');
    Route::post('/assignments/{assignment}/end', [AccountAssignmentController::class, 'end'])->name('assignments.end');
});
