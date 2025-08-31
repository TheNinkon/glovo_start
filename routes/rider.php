<?php

use App\Http\Controllers\Rider\ScheduleController;
use Illuminate\Support\Facades\Route;
// Apuntamos a la nueva ruta del controlador
use App\Http\Controllers\Rider\Dashboard\DashboardController;

/*
|--------------------------------------------------------------------------
| Rider Routes
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('rider.dashboard');
Route::post('/schedule', [ScheduleController::class, 'store'])->name('rider.schedule.store');

// Aquí irán todas las demás rutas para el rol de rider
