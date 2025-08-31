<?php
namespace App\Services;

use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    public function assignAccountToRider(Account $account, Rider $rider, Carbon $startDate, ?Carbon $endDate): Assignment
    {
        return DB::transaction(function () use ($account, $rider, $startDate, $endDate) {
            // Cierra cualquier asignación activa existente para esta cuenta
            $account->activeAssignment()->update(['end_date' => $startDate->copy()->subDay()]);

            // Crea la nueva asignación
            return Assignment::create([
                'account_id' => $account->id,
                'rider_id' => $rider->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        });
    }

    public function endAssignment(Assignment $assignment, ?Carbon $endDate = null): Assignment
    {
        $assignment->update(['end_date' => $endDate ?? Carbon::now()]);
        return $assignment;
    }
}
