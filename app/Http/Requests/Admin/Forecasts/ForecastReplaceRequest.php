<?php

namespace App\Http\Requests\Admin\Forecasts;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Forecast;

class ForecastReplaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $forecast = $this->route('forecast');
        if (!$user || !$forecast) return false;
        return $user->can('update', $forecast);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt,tsv'],
            'selection_deadline_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:draft,published,closed'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}

