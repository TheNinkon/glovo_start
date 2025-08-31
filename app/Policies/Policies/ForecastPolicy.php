<?php

namespace App\Policies\Policies;

use App\Models\Forecast;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ForecastPolicy
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
        return $user->can('forecasts.view') || $user->hasAnyRole(['super-admin', 'zone-manager', 'support', 'finance']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Forecast $forecast): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Permiso explícito o roles con atribuciones
        return $user->can('forecasts.create') || $user->can('forecasts.upload')
            || $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Forecast $forecast): bool
    {
        return $user->can('forecasts.manage') || $user->hasRole('zone-manager');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Forecast $forecast): bool
    {
        return $user->can('forecasts.manage') || $user->hasRole('zone-manager');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Forecast $forecast): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Forecast $forecast): bool
    {
        return false;
    }
}
