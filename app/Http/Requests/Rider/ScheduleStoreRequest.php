<?php
namespace App\Http\Requests\Rider;

use App\Rules\NoScheduleOverlap;
use App\Rules\SufficientCapacity;
use Illuminate\Foundation\Http\FormRequest;

class ScheduleStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'forecast_slot_id' => ['required', 'exists:forecast_slots,id', new SufficientCapacity, new NoScheduleOverlap],
        ];
    }
}
