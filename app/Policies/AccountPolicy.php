<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Account;

class AccountPolicy
{
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    public function view(User $user, Account $account): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Account $account): bool
    {
        return false;
    }

    public function delete(User $user, Account $account): bool
    {
        return false;
    }

    /**
     * Determina si el usuario puede crear asignaciones para una cuenta.
     */
    public function createAssignment(User $user, Account $account): bool
    {
        return $user->hasRole('zone-manager');
    }
}
