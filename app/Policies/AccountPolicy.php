<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Account;

class AccountPolicy
{
    /**
     * El método 'before' se ejecuta antes que cualquier otro.
     * Si el usuario es super-admin, le da acceso a todo y no sigue verificando.
     */
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    /**
     * Determina si el usuario puede ver la lista de cuentas.
     * Permitido para zone-manager, support y finance.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    /**
     * Determina si el usuario puede ver una cuenta específica.
     */
    public function view(User $user, Account $account): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    /**
     * Determina si el usuario puede crear cuentas.
     * Devuelve 'false' porque solo el super-admin puede (gestionado en 'before').
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede actualizar una cuenta.
     */
    public function update(User $user, Account $account): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede eliminar una cuenta.
     */
    public function delete(User $user, Account $account): bool
    {
        return false;
    }
        public function createAssignment(User $user, Account $account): bool
    {
        return $user->hasRole('zone-manager');
    }
}
