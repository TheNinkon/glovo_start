<?php

namespace App\Http\Controllers\Rider\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal para el rider.
     */
    public function index()
    {
        // Simplemente retornamos la vista. Nada más.
        return view('rider.Dashboard.index');
    }
}
