<?php

namespace App\Policies;

use App\Models\Rider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiderPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability): ?bool
    {
        // El super-admin tiene acceso a todo
        if ($user->hasRole('super-admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    public function view(User $user, Rider $rider): bool
    {
        // Riders solo pueden ver su propio perfil
        if ($user->hasRole('rider')) {
            return $user->id === $rider->user_id;
        }
        // Otros roles con permiso de lectura pueden ver cualquiera
        return $user->hasAnyRole(['zone-manager', 'support', 'finance']);
    }

    public function create(User $user): bool
    {
        // Solo super-admin puede crear (gestionado por el `before` hook)
        return false;
    }

    public function update(User $user, Rider $rider): bool
    {
        // Solo super-admin puede actualizar
        return false;
    }

    public function delete(User $user, Rider $rider): bool
    {
        // Solo super-admin puede eliminar
        return false;
    }
}
