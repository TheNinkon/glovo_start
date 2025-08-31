<?php
namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\AccountStoreRequest;
use App\Http\Requests\Admin\Accounts\AccountUpdateRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Account::class, 'account');
    }

    public function index(Request $request)
    {
        $accounts = Account::with('activeAssignment.rider')
            ->search($request->q)
            ->status($request->status)
            ->latest()
            ->paginate(10);
        return AccountResource::collection($accounts);
    }

    public function store(AccountStoreRequest $request)
    {
        $account = Account::create($request->validated());
        return new AccountResource($account);
    }

    public function show(Account $account)
    {
        return new AccountResource($account->load('activeAssignment.rider'));
    }

    public function update(AccountUpdateRequest $request, Account $account)
    {
        $account->update($request->validated());
        return new AccountResource($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return response()->noContent();
    }
}
