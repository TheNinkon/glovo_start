<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Riders\RiderController;
use App\Http\Controllers\Admin\Accounts\AccountController;
use App\Http\Controllers\Admin\Accounts\AccountAssignmentController;
use App\Http\Controllers\Admin\Forecasts\ForecastController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// --- DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');


// --- RUTAS DE VISTAS (devuelven HTML) ---
Route::get('/riders', [RiderController::class, 'index'])->name('admin.riders.index');
Route::get('/riders/{rider}', [RiderController::class, 'show'])->name('admin.riders.show');

Route::get('/accounts', [AccountController::class, 'index'])->name('admin.accounts.index');
Route::get('/accounts/{account}', [AccountController::class, 'show'])->name('admin.accounts.show');

// --- RUTAS PARA FORECASTS ---
Route::prefix('forecasts')->name('admin.forecasts.')->group(function () {
    Route::get('/', [ForecastController::class, 'index'])->name('index');
    Route::get('/create', [ForecastController::class, 'create'])->name('create');
    Route::post('/', [ForecastController::class, 'store'])
        ->middleware('permission:forecasts.create|forecasts.manage')
        ->name('store');
    Route::get('/{forecast}', [ForecastController::class, 'show'])->name('show');
    Route::patch('/{forecast}', [ForecastController::class, 'update'])
        ->middleware('permission:forecasts.manage|forecasts.create')
        ->name('update');
    Route::post('/upload', [ForecastController::class, 'upload'])
        ->middleware('permission:forecasts.upload|forecasts.create|forecasts.manage')
        ->name('upload');
    Route::post('/{forecast}/upload', [ForecastController::class, 'replace'])
        ->middleware('permission:forecasts.manage|forecasts.upload|forecasts.create')
        ->name('replace');
});


// --- RUTAS DE API (devuelven JSON) ---
Route::prefix('api')->name('admin.api.')->group(function () {

    // Endpoints para Riders
    Route::get('/riders', [RiderController::class, 'list'])->name('riders.list');
    Route::get('/riders/active-list', [RiderController::class, 'getActiveRiders'])->name('riders.active-list');
    Route::apiResource('riders', RiderController::class)->except(['index']);

    // Endpoints para Accounts
    Route::get('/accounts', [AccountController::class, 'list'])->name('accounts.list');
    Route::apiResource('accounts', AccountController::class)->except(['index']);

    // Endpoints para Assignments
    Route::get('/accounts/{account}/assignments', [AccountAssignmentController::class, 'index'])->name('accounts.assignments.index');
    Route::post('/accounts/{account}/assignments', [AccountAssignmentController::class, 'store'])->name('accounts.assignments.store');
    Route::post('/assignments/{assignment}/end', [AccountAssignmentController::class, 'end'])->name('assignments.end');
});
