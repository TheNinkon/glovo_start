<?php

namespace App\Rules;

use App\Models\Assignment;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoOverlappingAssignment implements ValidationRule
{
    protected $accountId;
    protected $ignoreAssignmentId;

    public function __construct($accountId, $ignoreAssignmentId = null)
    {
        $this->accountId = $accountId;
        $this->ignoreAssignmentId = $ignoreAssignmentId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $startDate = Carbon::parse(request()->input('start_date'));
        $endDate = request()->input('end_date') ? Carbon::parse(request()->input('end_date')) : null;

        $query = Assignment::where('account_id', $this->accountId)
            ->where(function ($query) use ($startDate, $endDate) {
                // Caso 1: La nueva asignación envuelve a una existente
                $query->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);

                // Caso 2: El inicio de la nueva asignación se solapa con una existente
                if ($endDate) {
                    $query->orWhereBetween('start_date', [$startDate, $endDate]);
                }

                // Caso 3: El fin de la nueva asignación se solapa con una existente
                if ($endDate) {
                    $query->orWhereBetween('end_date', [$startDate, $endDate]);
                }

                // Caso 4: Una asignación existente está contenida en la nueva
                 $query->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '>=', $startDate);
                    if ($endDate) {
                        $q->where('end_date', '<=', $endDate);
                    }
                 });
            });

        if ($this->ignoreAssignmentId) {
            $query->where('id', '!=', $this->ignoreAssignmentId);
        }

        if ($query->exists()) {
            $fail('The assignment period overlaps with an existing assignment for this account.');
        }
    }
}
