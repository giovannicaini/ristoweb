<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Cassa;
use App\Models\User;

class CassaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Cassa');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cassa $cassa): bool
    {
        return $user->checkPermissionTo('view Cassa');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Cassa');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cassa $cassa): bool
    {
        return $user->checkPermissionTo('update Cassa');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cassa $cassa): bool
    {
        return $user->checkPermissionTo('delete Cassa');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cassa $cassa): bool
    {
        return $user->checkPermissionTo('restore Cassa');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cassa $cassa): bool
    {
        return $user->checkPermissionTo('force-delete Cassa');
    }
}
