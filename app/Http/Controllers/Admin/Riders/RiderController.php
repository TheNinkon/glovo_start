<?php

namespace App\Http\Controllers\Admin\Riders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Riders\RiderStoreRequest;
use App\Http\Requests\Admin\Riders\RiderUpdateRequest;
use App\Http\Resources\RiderResource;
use App\Models\Rider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Rider::class, 'rider');
    }

    public function index(): View
    {
        $stats = [
            'total' => Rider::count(),
            'active' => Rider::where('status', 'active')->count(),
            'inactive' => Rider::where('status', 'inactive')->count(),
            'blocked' => Rider::where('status', 'blocked')->count(),
        ];
        return view('admin.riders.index', compact('stats'));
    }

    public function show(Request $request, Rider $rider)
    {
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            $rider->load('supervisor');
            return response()->json(['success' => true, 'data' => new RiderResource($rider)]);
        }
        $rider->load('supervisor', 'assignments.account');
        return view('admin.riders.show', compact('rider'));
    }

    // --- MÉTODOS DE LA API ---

    public function list(Request $request): JsonResponse
    {
        $query = Rider::with('supervisor')
            ->search($request->input('search.value'))
            ->filterByStatus($request->input('status'));
        $totalRecords = $query->count();
        $riders = $query->skip($request->input('start'))->take($request->input('length'))->latest()->get();
        $data = RiderResource::collection($riders);
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords,
            "data" => $data,
        ]);
    }

    /**
     * Devuelve una lista simple de riders activos para los dropdowns.
     */
    public function getActiveRiders(): JsonResponse
    {
        $this->authorize('viewAny', Rider::class);
        $riders = Rider::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        return response()->json($riders);
    }

    public function store(RiderStoreRequest $request): JsonResponse
    {
        $rider = Rider::create($request->validated());
        return response()->json(['message' => 'Rider created successfully.', 'rider' => new RiderResource($rider)], 201);
    }

    public function update(RiderUpdateRequest $request, Rider $rider): JsonResponse
    {
        $rider->update($request->validated());
        return response()->json(['message' => 'Rider updated successfully.', 'rider' => new RiderResource($rider)]);
    }

    public function destroy(Rider $rider): JsonResponse
    {
        $rider->delete();
        return response()->json(['message' => 'Rider deleted successfully.']);
    }
}
