<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // --- CORRECCIÓN AQUÍ ---
            // Permitimos el acceso a todos los roles de gestión al prefijo /admin.
            // Las Policies se encargarán de la seguridad específica de cada acción.
            Route::middleware(['web', 'auth', 'role:super-admin|zone-manager|support|finance'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            // Mantenemos las rutas específicas para otros roles
            Route::middleware(['web', 'auth', 'role:rider'])
                ->prefix('rider')
                ->group(base_path('routes/rider.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
