<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();

                // Lógica de redirección por rol para usuarios ya autenticados
                if ($user->hasRole('super-admin')) {
                    return redirect(route('admin.dashboard'));
                }
                if ($user->hasRole('zone-manager')) {
                    return redirect(route('zonemanager.dashboard'));
                }
                if ($user->hasRole('support')) {
                    return redirect(route('support.dashboard'));
                }
                if ($user->hasRole('finance')) {
                    return redirect(route('finance.dashboard'));
                }
                if ($user->hasRole('rider')) {
                    return redirect(route('rider.dashboard'));
                }

                // Redirección por defecto si no coincide ningún rol con dashboard
                return redirect('/');
            }
        }

        return $next($request);
    }
}
