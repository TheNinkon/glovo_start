<?php
namespace App\Rules;

use App\Models\ForecastSlot;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientCapacity implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slot = ForecastSlot::withCount(['riderSchedules' => fn($q) => $q->where('status', 'reserved')])->find($value);

        if ($slot && $slot->rider_schedules_count >= $slot->capacity) {
            $fail('This time slot is already at full capacity.');
        }
    }
}
