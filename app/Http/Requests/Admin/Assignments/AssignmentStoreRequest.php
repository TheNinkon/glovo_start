<?php

namespace App\Http\Requests\Admin\Assignments;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Assignment;
use App\Models\Account;

class AssignmentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        /** @var Account|null $account */
        $account = $this->route('account');

        // Autoriza si el usuario puede crear asignaciones en general o sobre esta cuenta
        return $user->can('create', Assignment::class) || ($account && $user->can('createAssignment', $account));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rider_id' => ['required', 'exists:riders,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
