<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Conto;
use App\Models\User;

class ContoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Conto');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Conto $conto): bool
    {
        return $user->checkPermissionTo('view Conto');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Conto');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Conto $conto): bool
    {
        return $user->checkPermissionTo('update Conto');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Conto $conto): bool
    {
        return $user->checkPermissionTo('delete Conto');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Conto $conto): bool
    {
        return $user->checkPermissionTo('restore Conto');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Conto $conto): bool
    {
        return $user->checkPermissionTo('force-delete Conto');
    }
}
