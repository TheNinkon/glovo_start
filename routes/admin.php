<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; // Asumiremos un controlador genérico por ahora

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
// Aquí irán todas las demás rutas para el super-admin
