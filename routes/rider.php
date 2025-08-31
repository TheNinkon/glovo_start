<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Rider Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'riderDashboard'])->name('rider.dashboard');
// Aquí irán todas las demás rutas para el rider
