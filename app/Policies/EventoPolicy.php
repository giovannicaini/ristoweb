<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Evento;
use App\Models\User;

class EventoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Evento');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Evento $evento): bool
    {
        return $user->checkPermissionTo('view Evento');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Evento');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Evento $evento): bool
    {
        return $user->checkPermissionTo('update Evento');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Evento $evento): bool
    {
        return $user->checkPermissionTo('delete Evento');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Evento $evento): bool
    {
        return $user->checkPermissionTo('restore Evento');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Evento $evento): bool
    {
        return $user->checkPermissionTo('force-delete Evento');
    }
}
