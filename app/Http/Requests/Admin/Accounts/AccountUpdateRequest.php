<?php
namespace App\Http\Requests\Admin\Accounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $accountId = $this->route('account')->id;
        return [
            'courier_id' => ['required', 'string', 'max:100', Rule::unique('accounts')->ignore($accountId)],
            'status' => 'required|in:active,inactive,blocked',
            'date_of_delivery' => 'nullable|date',
            'date_of_return' => 'nullable|date|after_or_equal:date_of_delivery',
        ];
    }
}
