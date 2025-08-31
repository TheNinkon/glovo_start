<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Forecast;

class ForecastStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) return false;
        // Autoriza a super-admin (por Gate::before) y a zone-manager para crear
        return $user->can('create', Forecast::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'selection_deadline_at' => ['required', 'date'],
            'status' => ['required', 'in:draft,published,closed'],
        ];
    }
}
