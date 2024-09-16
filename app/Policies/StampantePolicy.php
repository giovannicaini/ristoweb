<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Stampante;
use App\Models\User;

class StampantePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Stampante');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Stampante $stampante): bool
    {
        return $user->checkPermissionTo('view Stampante');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Stampante');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Stampante $stampante): bool
    {
        return $user->checkPermissionTo('update Stampante');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Stampante $stampante): bool
    {
        return $user->checkPermissionTo('delete Stampante');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Stampante $stampante): bool
    {
        return $user->checkPermissionTo('restore Stampante');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Stampante $stampante): bool
    {
        return $user->checkPermissionTo('force-delete Stampante');
    }
}
