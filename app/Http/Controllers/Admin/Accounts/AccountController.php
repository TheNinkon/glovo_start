<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Accounts\AccountStoreRequest;
use App\Http\Requests\Admin\Accounts\AccountUpdateRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct()
    {
        // Aplicamos la Policy a cada método para un control granular
        $this->authorizeResource(Account::class, 'account');
    }

    /**
     * Muestra la página de listado de Cuentas (la vista Blade).
     */
    public function index(): View
    {
        // Calculamos las estadísticas para las tarjetas de la vista
        $stats = [
            'total' => Account::count(),
            'active' => Account::where('status', 'active')->count(),
            'inactive' => Account::where('status', 'inactive')->count(),
            'blocked' => Account::where('status', 'blocked')->count(),
        ];

        return view('admin.accounts.index', compact('stats'));
    }

    /**
     * Muestra una cuenta (vista o JSON según la petición).
     */
    public function show(Request $request, Account $account)
    {
        // Si la petición viene de la API (buscando JSON para el formulario de edición)
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return new AccountResource($account);
        }

        // Si es una petición web normal, muestra la vista de detalle
        $account->load('activeAssignment.rider', 'assignments.rider');
        return view('admin.accounts.show', compact('account'));
    }


    // --- MÉTODOS DE LA API (PARA JAVASCRIPT) ---

    /**
     * Devuelve los datos de las cuentas en formato JSON para DataTables.
     */
    public function list(Request $request): JsonResponse
    {
        $query = Account::with('activeAssignment.rider')
            ->search($request->input('search.value'))
            ->status($request->input('status'));

        $totalRecords = $query->count();
        $accounts = $query->skip($request->input('start'))->take($request->input('length'))->latest()->get();

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => $totalRecords,
            "recordsFiltered" => $totalRecords, // Simplificado
            "data"            => AccountResource::collection($accounts),
        ]);
    }

    /**
     * Almacena una nueva cuenta.
     */
    public function store(AccountStoreRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());
        return response()->json(['message' => 'Account created successfully.', 'account' => new AccountResource($account)], 201);
    }

    /**
     * Actualiza una cuenta existente.
     */
    public function update(AccountUpdateRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());
        return response()->json(['message' => 'Account updated successfully.', 'account' => new AccountResource($account)]);
    }

    /**
     * Elimina una cuenta.
     */
    public function destroy(Account $account): JsonResponse
    {
        $account->delete();
        return response()->json(['message' => 'Account deleted successfully.']);
    }
}
