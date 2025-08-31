<?php
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\ScheduleStoreRequest;
use App\Http\Resources\Resources\RiderScheduleResource;
use App\Models\ForecastSlot;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function store(ScheduleStoreRequest $request)
    {
        $this->authorize('create', \App\Models\RiderSchedule::class);

        $rider = $request->user()->rider;
        $slot = ForecastSlot::find($request->forecast_slot_id);

        try {
            $schedule = $this->scheduleService->createSchedule($rider, $slot, $request->boolean('use_wildcard'));
            return new RiderScheduleResource($schedule);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
