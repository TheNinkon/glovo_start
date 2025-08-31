<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard principal para el administrador.
     */
    public function index()
    {
        return view('admin.Dashboard.index');
    }
}
