<?php
namespace App\Services;

use App\Models\Forecast;
use App\Models\ForecastSlot;
use App\Models\Rider;
use App\Models\RiderSchedule;
use App\Models\RiderWildcard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ScheduleService
{
    public function createSchedule(Rider $rider, ForecastSlot $slot, bool $useWildcard = false): RiderSchedule
    {
        return DB::transaction(function () use ($rider, $slot, $useWildcard) {
            $forecast = $slot->forecast;
            $isPastDeadline = $forecast->selection_deadline_at < Carbon::now();

            if ($isPastDeadline) {
                if (!$useWildcard) {
                    throw new Exception('Selection deadline has passed.');
                }

                $wildcard = RiderWildcard::firstOrCreate(
                    ['rider_id' => $rider->id, 'forecast_id' => $forecast->id]
                );

                if ($wildcard->tokens < 1) {
                    throw new Exception('Not enough wildcard tokens.');
                }
                $wildcard->decrement('tokens');
            }

            return RiderSchedule::create([
                'rider_id' => $rider->id,
                'forecast_slot_id' => $slot->id,
            ]);
        });
    }

    public function cancelSchedule(RiderSchedule $schedule): RiderSchedule
    {
        // Aquí podrías añadir lógica para devolver el comodín si aplica.
        $schedule->update(['status' => 'cancelled']);
        return $schedule;
    }
}
