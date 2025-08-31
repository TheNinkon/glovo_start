<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Assignment;

class AssignmentPolicy
{
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('zone-manager');
    }

    public function update(User $user, Assignment $assignment): bool // Se usará para 'end'
    {
        return $user->hasRole('zone-manager');
    }
}
