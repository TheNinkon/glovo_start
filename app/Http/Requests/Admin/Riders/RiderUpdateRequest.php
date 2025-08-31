<?php

namespace App\Http\Requests\Admin\Riders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RiderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $riderId = $this->route('rider')->id; // Obtiene el ID del rider desde la ruta

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('riders')->ignore($riderId),
            ],
            'phone' => 'nullable|string|max:30',
            'status' => 'required|in:active,inactive,blocked',
            'supervisor_id' => 'nullable|exists:riders,id',
            'contract_hours_per_week' => 'required|integer|min:0',
            'city_code' => 'nullable|in:FIG,GRO,CAL,MAT,BCN',
        ];
    }
}
