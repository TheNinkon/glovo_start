<?php

namespace App\Http\Controllers\Admin\Forecasts; // <-- Corregido

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\ForecastStoreRequest; // Esto está bien, el request puede ser genérico
use App\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Forecast::class);
        $forecasts = Forecast::latest()->paginate(20);
        return view('admin.forecasts.index', compact('forecasts')); // <-- Corregido
    }

    public function create(): View
    {
        $this->authorize('create', Forecast::class);
        return view('admin.forecasts.create'); // <-- Corregido
    }

    public function store(ForecastStoreRequest $request)
    {
        $this->authorize('create', Forecast::class);
        Forecast::create($request->validated());
        return redirect()->route('admin.forecasts.index')->with('success', 'Forecast created successfully.'); // <-- Corregido
    }

    public function show(Forecast $forecast): View
    {
        $this->authorize('view', $forecast);
        $slotsByDate = $forecast->slots()->orderBy('start_time')->get()->groupBy(fn($slot) => $slot->date->format('Y-m-d'));
        return view('admin.forecasts.show', compact('forecast', 'slotsByDate')); // <-- Corregido
    }
}
