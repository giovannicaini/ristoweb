<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Comanda;
use App\Models\User;

class ComandaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Comanda');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comanda $comanda): bool
    {
        return $user->checkPermissionTo('view Comanda');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Comanda');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comanda $comanda): bool
    {
        return $user->checkPermissionTo('update Comanda');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comanda $comanda): bool
    {
        return $user->checkPermissionTo('delete Comanda');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comanda $comanda): bool
    {
        return $user->checkPermissionTo('restore Comanda');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comanda $comanda): bool
    {
        return $user->checkPermissionTo('force-delete Comanda');
    }
}
