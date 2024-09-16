<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Postazione;
use App\Models\User;

class PostazionePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Postazione');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Postazione $postazione): bool
    {
        return $user->checkPermissionTo('view Postazione');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Postazione');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Postazione $postazione): bool
    {
        return $user->checkPermissionTo('update Postazione');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Postazione $postazione): bool
    {
        return $user->checkPermissionTo('delete Postazione');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Postazione $postazione): bool
    {
        return $user->checkPermissionTo('restore Postazione');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Postazione $postazione): bool
    {
        return $user->checkPermissionTo('force-delete Postazione');
    }
}
