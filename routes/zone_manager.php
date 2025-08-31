<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Zone Manager Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'zoneManagerDashboard'])->name('zonemanager.dashboard');
// Aquí irán todas las demás rutas para el zone-manager
