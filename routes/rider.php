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

// Weekly schedule UI
Route::get('/schedule', [ScheduleController::class, 'index'])->name('rider.schedule.index');

// API endpoints used by schedule UI
Route::get('/schedule/data', [ScheduleController::class, 'data'])->name('rider.schedule.data');
Route::get('/schedule/progress', [ScheduleController::class, 'progress'])->name('rider.schedule.progress');
Route::post('/schedule/select', [ScheduleController::class, 'select'])->name('rider.schedule.select');
Route::post('/schedule/deselect', [ScheduleController::class, 'deselect'])->name('rider.schedule.deselect');
Route::post('/schedule/commit', [ScheduleController::class, 'commit'])->name('rider.schedule.commit');

// Aquí irán todas las demás rutas para el rol de rider
