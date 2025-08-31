<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Support Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'supportDashboard'])->name('support.dashboard');
// Aquí irán todas las demás rutas para el support
