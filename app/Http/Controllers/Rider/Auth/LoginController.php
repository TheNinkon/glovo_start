<?php

namespace App\Http\Controllers\Rider\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login para el Rider.
     */
    public function showLoginForm()
    {
        // Apunta a la nueva vista específica para el login de Rider
        return view('rider.auth.login');
    }

    /**
     * Maneja la petición de login para el Rider.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember-me'))) {
            // Verificamos que el usuario que se loguea aquí tenga el rol 'rider'
            if (!Auth::user()->hasRole('rider')) {
                Auth::logout(); // Si no es rider, lo expulsamos
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            $request->session()->regenerate();
            // Redirige al dashboard de rider
            return redirect()->intended(route('rider.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Cierra la sesión del Rider.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Al hacer logout, lo mandamos al login de rider (la raíz)
        return redirect('/');
    }
}
