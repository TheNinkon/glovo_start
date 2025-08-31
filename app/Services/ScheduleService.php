<?php
namespace App\Services;

use App\Models\Forecast;
use App\Models\ForecastSlot;
use App\Models\Rider;
use App\Models\RiderForecastState;
use App\Models\RiderSchedule;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Reserve a slot for a rider with quota, capacity, and overlap checks.
     */
    public function reserve(Rider $rider, ForecastSlot $slot): RiderSchedule
    {
        return DB::transaction(function () use ($rider, $slot) {
            // Lock slot row for capacity check
            $lockedSlot = ForecastSlot::where('id', $slot->id)->lockForUpdate()->firstOrFail();
            $forecast = $lockedSlot->forecast;

            if ($forecast->status !== 'published') {
                throw new Exception('Forecast is not open for selection.');
            }

            // City enforcement: rider must belong to the same city as the forecast
            if (!empty($forecast->city_code) && !empty($rider->city_code) && $forecast->city_code !== $rider->city_code) {
                throw new Exception('This forecast belongs to city ' . $forecast->city_code . ' and you belong to ' . ($rider->city_code ?: 'N/A') . '.');
            }

            // State for this rider + forecast
            $state = RiderForecastState::firstOrCreate(
                ['rider_id' => $rider->id, 'forecast_id' => $forecast->id],
                [
                    'required_weekly_minutes' => max(0, (int)$rider->contract_hours_per_week) * 60,
                    'reserved_weekly_minutes' => 0,
                    'wildcards_remaining' => config('scheduling.default_wildcards_per_forecast', 5),
                ]
            );

            // If a record already exists for this rider+slot, handle idempotently
            $existing = RiderSchedule::where('rider_id', $rider->id)
                ->where('forecast_slot_id', $lockedSlot->id)
                ->first();

            if ($existing) {
                if ($existing->status === 'reserved') {
                    // Already reserved; idempotent success without changing minutes
                    return $existing;
                }
                // Reactivate a cancelled reservation: validate quota and capacity before flipping
                $minutes = $lockedSlot->duration_minutes;
                $newTotal = $state->reserved_weekly_minutes + $minutes;
                if ($newTotal > $state->required_weekly_minutes) {
                    throw new Exception('Selecting this slot exceeds your weekly contract hours.');
                }
                $assigned = $lockedSlot->riderSchedules()->where('status', 'reserved')->count();
                if ($assigned >= $lockedSlot->capacity) {
                    throw new Exception('Slot is at full capacity.');
                }
                $existing->update(['status' => 'reserved']);
                $state->reserved_weekly_minutes = $newTotal;
                $state->save();
                if ($forecast->selection_deadline_at && $forecast->selection_deadline_at < Carbon::now() && !$state->locked_at) {
                    $state->locked_at = Carbon::now();
                    $state->save();
                }
                return $existing;
            }

            // Capacity check for a fresh reservation
            $assigned = $lockedSlot->riderSchedules()->where('status', 'reserved')->count();
            if ($assigned >= $lockedSlot->capacity) {
                throw new Exception('Slot is at full capacity.');
            }

            // Overlap check: same date overlapping time range for this rider
            $overlap = RiderSchedule::where('rider_id', $rider->id)
                ->where('status', 'reserved')
                ->whereHas('forecastSlot', function ($q) use ($lockedSlot) {
                    $q->where('date', $lockedSlot->date)
                      ->where(function ($qq) use ($lockedSlot) {
                          $qq->where('start_time', '<', $lockedSlot->end_time)
                             ->where('end_time', '>', $lockedSlot->start_time);
                      });
                })->exists();
            if ($overlap) {
                throw new Exception('This slot overlaps with an existing reservation.');
            }

            $minutes = $lockedSlot->duration_minutes;
            $newTotal = $state->reserved_weekly_minutes + $minutes;
            if ($newTotal > $state->required_weekly_minutes) {
                throw new Exception('Selecting this slot exceeds your weekly contract hours.');
            }

            // Daily limit: max 8h/day (480 minutes) per Spanish labour rules
            $dayMinutes = RiderSchedule::where('rider_id', $rider->id)
                ->where('status', 'reserved')
                ->whereHas('forecastSlot', function($q) use ($lockedSlot) {
                    $q->whereDate('date', $lockedSlot->date);
                })
                ->get()
                ->sum(function($s) { return $s->forecastSlot?->duration_minutes ?? 0; });
            if (($dayMinutes + $minutes) > 480) {
                throw new Exception('No puedes reservar más de 8 horas en un mismo día.');
            }

            $schedule = RiderSchedule::create([
                'rider_id' => $rider->id,
                'forecast_slot_id' => $lockedSlot->id,
            ]);

            $state->reserved_weekly_minutes = $newTotal;
            $state->save();

            // Auto-lock after deadline
            if ($forecast->selection_deadline_at && $forecast->selection_deadline_at < Carbon::now() && !$state->locked_at) {
                $state->locked_at = Carbon::now();
                $state->save();
            }

            return $schedule;
        });
    }

    /**
     * Cancel a reservation. May consume a wildcard if locked or past deadline.
     */
    public function cancel(Rider $rider, RiderSchedule $schedule): void
    {
        DB::transaction(function () use ($rider, $schedule) {
            if ($schedule->rider_id !== $rider->id) {
                throw new Exception('You can only cancel your own reservations.');
            }

            $slot = $schedule->forecastSlot;
            $forecast = $slot->forecast;
            $state = RiderForecastState::firstOrCreate(
                ['rider_id' => $rider->id, 'forecast_id' => $forecast->id],
                [
                    'required_weekly_minutes' => max(0, (int)$rider->contract_hours_per_week) * 60,
                    'reserved_weekly_minutes' => 0,
                    'wildcards_remaining' => config('scheduling.default_wildcards_per_forecast', 5),
                ]
            );

            $requiresWildcard = $state->locked_at !== null || ($forecast->selection_deadline_at && $forecast->selection_deadline_at < Carbon::now());
            if ($requiresWildcard) {
                if ($state->wildcards_remaining < 1) {
                    throw new Exception('No wildcards remaining to cancel after deadline.');
                }
                $state->wildcards_remaining -= 1;
            }

            // Cancel
            $schedule->update(['status' => 'cancelled']);

            // Update minutes
            $state->reserved_weekly_minutes = max(0, $state->reserved_weekly_minutes - $slot->duration_minutes);
            $state->save();
        });
    }

    /**
     * Commit a rider weekly schedule. Requires exact completion if configured.
     */
    public function commit(Rider $rider, Forecast $forecast): void
    {
        DB::transaction(function () use ($rider, $forecast) {
            if (!empty($forecast->city_code) and !empty($rider->city_code) and $forecast->city_code !== $rider->city_code) {
                throw new Exception('No puedes confirmar horarios de un forecast de otra ciudad.');
            }
            $state = RiderForecastState::firstOrCreate(
                ['rider_id' => $rider->id, 'forecast_id' => $forecast->id],
                [
                    'required_weekly_minutes' => max(0, (int)$rider->contract_hours_per_week) * 60,
                    'reserved_weekly_minutes' => 0,
                    'wildcards_remaining' => config('scheduling.default_wildcards_per_forecast', 5),
                ]
            );

            if (config('scheduling.enforce_exact_contract_hours', true)) {
                if ($state->reserved_weekly_minutes !== $state->required_weekly_minutes) {
                    throw new Exception('Debe completar exactamente sus horas contratadas para confirmar.');
                }
            } else {
                if ($state->reserved_weekly_minutes < $state->required_weekly_minutes) {
                    throw new Exception('Horas reservadas insuficientes para confirmar.');
                }
            }

            if (!$state->locked_at) {
                $state->locked_at = Carbon::now();
                $state->save();
            }

            // Weekly rest rule: at least 36h consecutive without reservations per week
            $weekStart = Carbon::parse($forecast->week_start)->startOfDay();
            $weekEnd = Carbon::parse($forecast->week_end)->endOfDay();
            $reservations = RiderSchedule::where('rider_id', $rider->id)
                ->where('status', 'reserved')
                ->whereHas('forecastSlot', function($q) use ($forecast) {
                    $q->where('forecast_id', $forecast->id);
                })
                ->with('forecastSlot')
                ->get()
                ->map(function($s){
                    $d = Carbon::parse($s->forecastSlot->date);
                    $start = Carbon::parse($d->toDateString().' '.$s->forecastSlot->start_time);
                    $end = Carbon::parse($d->toDateString().' '.$s->forecastSlot->end_time);
                    return [$start, $end];
                })
                ->sortBy(fn($r) => $r[0]->timestamp)
                ->values();

            // Compute max free gap
            $last = $weekStart->copy();
            $maxGap = 0; // minutes
            foreach ($reservations as [$s,$e]) {
                $gap = max(0, $s->diffInMinutes($last));
                if ($gap > $maxGap) $maxGap = $gap;
                if ($e->gt($last)) $last = $e->copy();
            }
            $tailGap = max(0, $weekEnd->diffInMinutes($last));
            if ($tailGap > $maxGap) $maxGap = $tailGap;
            if ($maxGap < (36*60)) {
                throw new Exception('Debe existir un descanso mínimo de 36 horas seguidas en la semana para confirmar.');
            }
        });
    }
}
