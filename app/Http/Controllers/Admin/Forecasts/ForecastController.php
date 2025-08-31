<?php

namespace App\Http\Controllers\Admin\Forecasts; // <-- Corregido

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\ForecastStoreRequest; // Esto está bien, el request puede ser genérico
use App\Models\Forecast;
use App\Services\ForecastImportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(): View
    {
        $forecasts = Forecast::latest()->paginate(20);
        return view('admin.forecasts.index', compact('forecasts')); // <-- Corregido
    }

    public function create(): View
    {
        return view('admin.forecasts.create'); // <-- Corregido
    }

    public function update(\Illuminate\Http\Request $request, Forecast $forecast)
    {
        $this->authorize('update', $forecast);
        $data = $request->validate([
            'name' => ['nullable','string','max:255'],
            'status' => ['nullable','in:draft,published,closed'],
            'selection_deadline_at' => ['nullable','date'],
            'city_code' => ['nullable','in:FIG,GRO,CAL,MAT,BCN'],
        ]);
        if (isset($data['selection_deadline_at'])) {
            $data['selection_deadline_at'] = \Carbon\Carbon::parse($data['selection_deadline_at']);
        }
        $forecast->fill(array_filter($data, fn($v) => $v !== null));
        $forecast->save();
        return redirect()->back()->with('success','Forecast updated.');
    }

    public function store(ForecastStoreRequest $request)
    {
        Forecast::create($request->validated());
        return redirect()->route('admin.forecasts.index')->with('success', 'Forecast created successfully.'); // <-- Corregido
    }

    public function show(Forecast $forecast): View
    {
        $slotsByDate = $forecast->slots()->orderBy('start_time')->get()->groupBy(fn($slot) => $slot->date->format('Y-m-d'));
        return view('admin.forecasts.show', compact('forecast', 'slotsByDate')); // <-- Corregido
    }

    /**
     * Upload a forecast from a CSV/TSV exported from Excel.
     */
    public function upload(\App\Http\Requests\Admin\Forecasts\ForecastUploadRequest $request, ForecastImportService $importService)
    {
        $data = $request->validated();

        $forecast = $importService->import(
            filePath: $request->file('file')->getRealPath(),
            weekStart: \Carbon\Carbon::parse($data['week_start'])->startOfWeek(),
            name: $data['name'] ?? null,
            selectionDeadlineAt: \Carbon\Carbon::parse($data['selection_deadline_at']),
            status: $data['status'] ?? 'published',
            cityCode: $data['city_code'] ?? null
        );

        return redirect()->route('admin.forecasts.show', $forecast)->with('success', 'Forecast imported successfully.');
    }

    /**
     * Replace slots of an existing forecast from an uploaded file.
     */
    public function replace(\App\Http\Requests\Admin\Forecasts\ForecastReplaceRequest $request, Forecast $forecast, ForecastImportService $importService)
    {
        $importService->replaceSlots($forecast, $request->file('file')->getRealPath());

        if ($request->filled('status')) {
            $forecast->status = $request->string('status');
        }
        if ($request->filled('selection_deadline_at')) {
            $forecast->selection_deadline_at = \Carbon\Carbon::parse($request->input('selection_deadline_at'));
        }
        if ($request->filled('name')) {
            $forecast->name = $request->string('name');
        }
        $forecast->save();

        return redirect()->route('admin.forecasts.show', $forecast)->with('success', 'Forecast slots replaced successfully.');
    }
}
