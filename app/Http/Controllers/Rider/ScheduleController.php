<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\ScheduleStoreRequest;
use App\Http\Requests\Rider\ScheduleCommitRequest;
use App\Http\Resources\Resources\RiderScheduleResource;
use App\Models\Forecast;
use App\Models\ForecastSlot;
use App\Models\RiderSchedule as RiderScheduleModel;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $rider = $user->rider;
        $missingRiderProfile = !$rider;

        $requested = $request->query('week');
        $today = $requested ? Carbon::parse($requested) : Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        // Límite de navegación: semana actual y 2 futuras
        $minWeek = Carbon::now()->startOfWeek();
        $maxWeek = $minWeek->copy()->addWeeks(2);

        $forecast = Forecast::published()->activeWeek($today)->first();
        $deadline = $forecast?->selection_deadline_at;
        $isLocked = $deadline ? $deadline < Carbon::now() : false;
        $forecast_id = $forecast?->id;

        $prevWeek = $startOfWeek->gt($minWeek)
            ? route('rider.schedule.index', ['week' => $startOfWeek->copy()->subWeek()->toDateString()])
            : null;
        $nextWeek = $startOfWeek->lt($maxWeek)
            ? route('rider.schedule.index', ['week' => $startOfWeek->copy()->addWeek()->toDateString()])
            : null;

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $startOfWeek->copy()->addDays($i);
            $weekDates[] = [
                'full' => $d->toDateString(),
                'dayName' => $d->format('D'),
                'dayNum' => $d->format('d')
            ];
        }

        $summary = [
            'contractedHours' => 0,
            'reservedHours' => 0,
            'wildcards' => 0,
        ];
        if ($forecast && $rider) {
            $reservedCount = RiderScheduleModel::where('rider_id', $rider->id)
                ->whereHas('forecastSlot', fn($q) => $q->where('forecast_id', $forecast->id))
                ->where('status', 'reserved')->count();
            $summary['reservedHours'] = $reservedCount * 0.5;
            $summary['wildcards'] = optional(\App\Models\RiderWildcard::where('rider_id', $rider->id)
                ->where('forecast_id', $forecast->id)->first())->tokens ?? 0;
        }

        // defaultDay: hoy si es la semana actual; si es futura, el lunes (week_start)
        $defaultDay = $startOfWeek->equalTo($minWeek) ? Carbon::now()->toDateString() : $startOfWeek->toDateString();
        $scheduleData = null;

        return view('rider.schedule.index', compact(
            'missingRiderProfile', 'forecast_id', 'isLocked', 'defaultDay', 'startOfWeek', 'endOfWeek',
            'prevWeek', 'nextWeek', 'deadline', 'weekDates', 'scheduleData', 'summary'
        ));
    }

    // Legacy store (unused by new UI but kept for compatibility)
    public function store(ScheduleStoreRequest $request)
    {
        $this->authorize('create', \App\Models\RiderSchedule::class);
        $rider = $request->user()->rider;
        if (!$rider) {
            return response()->json(['message' => 'Rider profile not found. Please contact support.'], 422);
        }
        $slot = ForecastSlot::find($request->forecast_slot_id);
        try {
            $schedule = $this->scheduleService->createSchedule($rider, $slot, $request->boolean('use_wildcard'));
            return new RiderScheduleResource($schedule);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $rider = $user->rider;
        $day = $request->query('week') ? Carbon::parse($request->query('week')) : Carbon::now();
        // Limitar a semana actual y 2 futuras
        $minWeek = Carbon::now()->startOfWeek();
        $maxWeek = $minWeek->copy()->addWeeks(2);
        if ($day->lt($minWeek)) { $day = $minWeek->copy(); }
        if ($day->gt($maxWeek)) { $day = $maxWeek->copy(); }

        $forecast = Forecast::published()->activeWeek($day)
            ->when($rider && $rider->city_code, fn($q) => $q->where('city_code', $rider->city_code))
            ->first();
        if (!$forecast) {
            // Fallback a la próxima semana publicada de la misma ciudad
            $startOfWeek = $day->copy()->startOfWeek();
            $next = Forecast::published()
                ->when($rider && $rider->city_code, fn($q) => $q->where('city_code', $rider->city_code))
                ->where('week_start', '>=', $startOfWeek)
                ->orderBy('week_start')
                ->first();
            if ($next) {
                return response()->json([
                    'success' => false,
                    'redirect_week' => $next->week_start->toDateString(),
                    'message' => 'Redirigiendo a la próxima semana con forecast publicado.'
                ]);
            }
            return response()->json(['success' => false, 'message' => 'No hay un horario disponible para esta semana.']);
        }

        $slots = ForecastSlot::withCount(['riderSchedules' => function ($q) {
            $q->where('status', 'reserved');
        }])->where('forecast_id', $forecast->id)
          ->orderBy('date')->orderBy('start_time')->get();

        $byDate = $slots->groupBy(fn($s) => $s->date->format('Y-m-d'));
        $scheduleData = [];
        foreach ($byDate as $dateStr => $daySlots) {
            $dayData = [
                'date' => $dateStr,
                'dayName' => Carbon::parse($dateStr)->format('l'),
                'slots' => []
            ];
            foreach ($daySlots as $slot) {
                $isMine = $rider ? RiderScheduleModel::where('rider_id', $rider->id)
                    ->where('forecast_slot_id', $slot->id)->where('status', 'reserved')->exists() : false;
                $available = ($slot->rider_schedules_count ?? 0) < $slot->capacity;
                $status = $isMine ? 'mine' : ($available ? 'available' : 'locked');
                $dayData['slots'][] = [
                    'time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'identifier' => (string)$slot->id,
                    'status' => $status,
                    'capacity' => (int) $slot->capacity,
                    'assigned' => (int) ($slot->rider_schedules_count ?? 0),
                ];
            }
            $scheduleData[] = $dayData;
        }

        $reservedCount = $rider ? RiderScheduleModel::where('rider_id', $rider->id)
            ->whereHas('forecastSlot', fn($q) => $q->where('forecast_id', $forecast->id))
            ->where('status', 'reserved')->count() : 0;

        $wildcards = $rider ? optional(\App\Models\RiderWildcard::where('rider_id', $rider->id)
            ->where('forecast_id', $forecast->id)->first())->tokens ?? 0 : 0;

        return response()->json([
            'success' => true,
            'forecast_id' => $forecast->id,
            'deadline' => optional($forecast->selection_deadline_at)->toIso8601String(),
            'isLocked' => $forecast->selection_deadline_at < Carbon::now(),
            'contractedHours' => (int) ($rider?->contract_hours_per_week ?? 0),
            'reservedHours' => $reservedCount * 0.5,
            'wildcards' => $wildcards,
            'scheduleData' => $scheduleData,
        ]);
    }

    public function select(Request $request)
    {
        // Authorization is already enforced by route middleware 'role:rider'
        $rider = $request->user()->rider;
        if (!$rider) {
            return response()->json(['message' => 'Perfil de rider no encontrado.'], 422);
        }
        $slotId = $request->input('slot');
        $useWildcard = (bool)$request->input('use_wildcard', false);
        $slot = ForecastSlot::find($slotId);
        if (!$slot) return response()->json(['message' => 'Slot inválido.'], 422);

        $validator = Validator::make(['forecast_slot_id' => $slotId], [
            'forecast_slot_id' => ['required', 'exists:forecast_slots,id', new \App\Rules\SufficientCapacity, new \App\Rules\NoScheduleOverlap]
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $schedule = $this->scheduleService->reserve($rider, $slot);
            return response()->json(['message' => 'Hora reservada correctamente.', 'data' => new RiderScheduleResource($schedule)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function deselect(Request $request)
    {
        // Authorization is already enforced by route middleware 'role:rider'
        $rider = $request->user()->rider;
        if (!$rider) {
            return response()->json(['message' => 'Perfil de rider no encontrado.'], 422);
        }
        $slotId = $request->input('slot');
        $schedule = RiderScheduleModel::where('rider_id', $rider->id)
            ->where('forecast_slot_id', $slotId)
            ->where('status', 'reserved')->first();
        if (!$schedule) {
            return response()->json(['message' => 'Reserva no encontrada.'], 404);
        }
        $forecast = $schedule->forecastSlot->forecast;
        if ($forecast->selection_deadline_at < Carbon::now()) {
            return response()->json(['message' => 'No puedes cancelar después del plazo.'], 422);
        }
        $this->scheduleService->cancel($rider, $schedule);
        return response()->json(['message' => 'Reserva cancelada.']);
    }

    public function progress(Request $request)
    {
        $rider = $request->user()->rider;
        $forecastId = $request->query('forecast_id');
        $forecast = $forecastId ? Forecast::find($forecastId) : null;
        if ($rider && $forecast && $rider->city_code && $forecast->city_code && $rider->city_code !== $forecast->city_code) {
            return response()->json(['success' => false, 'message' => 'No puedes ver el progreso de un forecast de otra ciudad.'], 403);
        }
        if (!$rider || !$forecast) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.'], 422);
        }
        $state = \App\Models\RiderForecastState::firstOrCreate(
            ['rider_id' => $rider->id, 'forecast_id' => $forecast->id],
            [
                'required_weekly_minutes' => max(0, (int)$rider->contract_hours_per_week) * 60,
                'reserved_weekly_minutes' => 0,
                'wildcards_remaining' => config('scheduling.default_wildcards_per_forecast', 5),
            ]
        );
        $slots = RiderScheduleModel::with('forecastSlot')
            ->where('rider_id', $rider->id)
            ->whereHas('forecastSlot', fn($q) => $q->where('forecast_id', $forecast->id))
            ->where('status', 'reserved')->get();
        return response()->json([
            'success' => true,
            'required_minutes' => $state->required_weekly_minutes,
            'reserved_minutes' => $state->reserved_weekly_minutes,
            'locked' => (bool)$state->locked_at,
            'wildcards_remaining' => $state->wildcards_remaining,
            'slots' => $slots->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->forecastSlot->date->toDateString(),
                'time' => Carbon::parse($s->forecastSlot->start_time)->format('H:i'),
                'duration' => $s->forecastSlot->duration_minutes,
            ]),
        ]);
    }

    public function commit(ScheduleCommitRequest $request)
    {
        $rider = $request->user()->rider;
        $forecast = Forecast::find($request->integer('forecast_id'));
        if (!$rider || !$forecast) return response()->json(['success' => false, 'message' => 'Datos inválidos.'], 422);
        try {
            $this->scheduleService->commit($rider, $forecast);
            return response()->json(['success' => true, 'message' => 'Horarios confirmados.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
