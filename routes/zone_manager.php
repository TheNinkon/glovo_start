<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZoneManager\WildcardController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Zone Manager Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'zoneManagerDashboard'])->name('zonemanager.dashboard');
// Wildcards management
Route::post('/wildcards', [\App\Http\Controllers\ZoneManager\WildcardController::class, 'store'])->name('zonemanager.wildcards.store');
// Aquí irán todas las demás rutas para el zone-manager
Route::post('/wildcards', [WildcardController::class, 'store'])->name('zonemanager.wildcards.store');
