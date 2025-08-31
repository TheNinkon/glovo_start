<?php
namespace App\Rules;

use App\Models\ForecastSlot;
use App\Models\RiderSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoScheduleOverlap implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slotToBook = ForecastSlot::find($value);
        if (!$slotToBook) { return; }

        $rider = auth()->user()->rider ?? null;
        if (!$rider) return; // Sin perfil no validamos aquí

        $hasOverlap = RiderSchedule::where('rider_id', $rider->id)
            ->where('status', 'reserved')
            ->whereHas('forecastSlot', function ($query) use ($slotToBook) {
                $query->where('date', $slotToBook->date)
                      ->where(function ($q) use ($slotToBook) {
                          $q->where(function ($q2) use ($slotToBook) {
                                $q2->where('start_time', '<', $slotToBook->end_time)
                                   ->where('end_time', '>', $slotToBook->start_time);
                            });
                      });
            })
            ->exists();

        if ($hasOverlap) {
            $fail('This time slot overlaps with another reserved schedule.');
        }
    }
}

