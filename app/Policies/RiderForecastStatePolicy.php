<?php

namespace App\Policies;

use App\Models\RiderForecastState;
use App\Models\User;

class RiderForecastStatePolicy
{
    public function before(User $user, $ability): ?bool
    {
        return $user->hasRole('super-admin') ? true : null;
    }

    public function view(User $user, RiderForecastState $state): bool
    {
        return $user->hasRole('rider') && $user->rider && $user->rider->id === $state->rider_id;
    }

    public function update(User $user, RiderForecastState $state): bool
    {
        // rider can update (commit) own; zone-manager handled via before/middleware
        return $this->view($user, $state);
    }
}

