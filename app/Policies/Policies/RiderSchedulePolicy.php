<?php

namespace App\Policies\Policies;

use App\Models\RiderSchedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RiderSchedulePolicy
{
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RiderSchedule $riderSchedule): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Riders pueden crear sus propios horarios
        return $user->hasRole('rider');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RiderSchedule $riderSchedule): bool
    {
        return $user->hasRole('zone-manager');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, RiderSchedule $riderSchedule): bool
    {
        return $user->hasRole('zone-manager');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, RiderSchedule $riderSchedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, RiderSchedule $riderSchedule): bool
    {
        return false;
    }
}
