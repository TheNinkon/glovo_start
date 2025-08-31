<?php

namespace App\Http\Requests\Rider;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('rider') ?? false;
    }

    public function rules(): array
    {
        return [
            // schedule bound in the route
        ];
    }
}

