<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ComandaDettaglio;
use App\Models\User;

class ComandaDettaglioPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ComandaDettaglio');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ComandaDettaglio $comandadettaglio): bool
    {
        return $user->checkPermissionTo('view ComandaDettaglio');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ComandaDettaglio');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ComandaDettaglio $comandadettaglio): bool
    {
        return $user->checkPermissionTo('update ComandaDettaglio');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ComandaDettaglio $comandadettaglio): bool
    {
        return $user->checkPermissionTo('delete ComandaDettaglio');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ComandaDettaglio $comandadettaglio): bool
    {
        return $user->checkPermissionTo('restore ComandaDettaglio');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ComandaDettaglio $comandadettaglio): bool
    {
        return $user->checkPermissionTo('force-delete ComandaDettaglio');
    }
}
