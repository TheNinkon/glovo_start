<?php

namespace App\Http\Requests\Admin\Forecasts;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Forecast;

class ForecastUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        // Permitir via policy (create) o permiso directo de upload
        return $user->can('create', Forecast::class) || $user->can('forecasts.upload');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,tsv'],
            'week_start' => ['required', 'date'],
            'selection_deadline_at' => ['required', 'date'],
            'status' => ['required', 'in:draft,published,closed'],
            'name' => ['nullable', 'string', 'max:255'],
            'city_code' => ['required', 'in:FIG,GRO,CAL,MAT,BCN'],
        ];
    }
}
