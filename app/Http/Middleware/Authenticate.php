<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si la petición no espera una respuesta JSON,
        // redirige a la ruta de login del administrador.
        return $request->expectsJson() ? null : route('admin.login');
    }
}
