<?php

namespace App\Http\Requests\Admin\Riders;

use Illuminate\Foundation\Http\FormRequest;

class RiderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja en la Policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:riders,email',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|in:active,inactive,blocked',
            'supervisor_id' => 'nullable|exists:riders,id',
            'contract_hours_per_week' => 'required|integer|min:0',
            'city_code' => 'nullable|in:FIG,GRO,CAL,MAT,BCN',
        ];
    }
}
