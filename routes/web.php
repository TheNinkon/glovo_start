<?php

use Illuminate\Support\Facades\Route;

// Importamos los controladores de autenticación con alias para mayor claridad
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Rider\Auth\LoginController as RiderLoginController;

/*
|--------------------------------------------------------------------------
| Rutas Web
|--------------------------------------------------------------------------
*/

// --- RUTAS DE LOGIN PARA RIDERS (en la raíz del sitio) ---
Route::get('/', [RiderLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/', [RiderLoginController::class, 'login'])->middleware('guest');
Route::post('/rider/logout', [RiderLoginController::class, 'logout'])->name('rider.logout')->middleware('auth');

// --- RUTAS DE LOGIN PARA ADMIN (con prefijo /admin) ---
Route::prefix('admin')->as('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
    Route::post('/login', [AdminLoginController::class, 'login'])->middleware('guest');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout')->middleware('auth');
});
