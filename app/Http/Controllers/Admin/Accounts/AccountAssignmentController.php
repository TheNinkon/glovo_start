<?php
namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Assignments\AssignmentStoreRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountAssignmentController extends Controller
{
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function index(Account $account)
    {
        $this->authorize('view', $account);
        $assignments = $account->assignments()->with('rider')->latest('start_date')->paginate(10);
        return AssignmentResource::collection($assignments);
    }

    public function store(AssignmentStoreRequest $request, Account $account)
    {
        $this->authorize('create', Assignment::class);

        $rider = Rider::find($request->rider_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : null;

        $assignment = $this->assignmentService->assignAccountToRider($account, $rider, $startDate, $endDate);

        return new AssignmentResource($assignment);
    }

    public function end(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        $this->assignmentService->endAssignment($assignment);
        return new AssignmentResource($assignment);
    }
}
