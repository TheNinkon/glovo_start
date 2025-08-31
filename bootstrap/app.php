<?php

use Illuminate\Support\Facades\Route; // <-- AÑADE ESTA LÍNEA
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Rutas para Super Admin
            Route::middleware(['web', 'auth', 'role:super-admin'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            // Rutas para Zone Manager
            Route::middleware(['web', 'auth', 'role:zone-manager'])
                ->prefix('zone-manager')
                ->group(base_path('routes/zone_manager.php'));

            // Rutas para Support
            Route::middleware(['web', 'auth', 'role:support'])
                ->prefix('support')
                ->group(base_path('routes/support.php'));

            // Rutas para Finance
            Route::middleware(['web', 'auth', 'role:finance'])
                ->prefix('finance')
                ->group(base_path('routes/finance.php'));

            // Rutas para Rider
            Route::middleware(['web', 'auth', 'role:rider'])
                ->prefix('rider')
                ->group(base_path('routes/rider.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        // Alias para los middlewares
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
