<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Usamos View::composer para compartir datos con todas las vistas
        View::composer('*', function ($view) {
            // Solo procedemos si hay un usuario autenticado
            if (Auth::check()) {
                $user = Auth::user();
                $menuFileName = '';

                // --- LÓGICA PARA SELECCIONAR EL MENÚ SEGÚN EL ROL ---
                if ($user->hasRole('super-admin')) {
                    $menuFileName = 'adminMenu.json';
                } elseif ($user->hasRole('rider')) {
                    $menuFileName = 'riderMenu.json';
                }
                // ... puedes añadir más roles aquí con elseif ...
                // elseif ($user->hasRole('support')) {
                //     $menuFileName = 'supportMenu.json';
                // }

                $verticalMenuData = null;
                $horizontalMenuData = null;

                // Si se encontró un archivo de menú para el rol, lo cargamos
                if ($menuFileName && file_exists(resource_path('menu/' . $menuFileName))) {
                    $menuJson = file_get_contents(resource_path('menu/' . $menuFileName));
                    $verticalMenuData = json_decode($menuJson);
                    $horizontalMenuData = json_decode($menuJson); // Usamos el mismo para ambos layouts
                }

                // Compartimos la data del menú con las vistas
                $view->with('menuData', $verticalMenuData);
                $view->with('menuHorizontalData', $horizontalMenuData);

            }
        });
    }
}
