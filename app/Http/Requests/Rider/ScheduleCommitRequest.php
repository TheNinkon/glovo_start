<?php

namespace App\Http\Requests\Rider;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleCommitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('rider') ?? false;
    }

    public function rules(): array
    {
        return [
            'forecast_id' => ['required', 'exists:forecasts,id'],
        ];
    }
}

