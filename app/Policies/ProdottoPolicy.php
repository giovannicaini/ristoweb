<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Prodotto;
use App\Models\User;

class ProdottoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Prodotto');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prodotto $prodotto): bool
    {
        return $user->checkPermissionTo('view Prodotto');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Prodotto');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prodotto $prodotto): bool
    {
        return $user->checkPermissionTo('update Prodotto');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prodotto $prodotto): bool
    {
        return $user->checkPermissionTo('delete Prodotto');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prodotto $prodotto): bool
    {
        return $user->checkPermissionTo('restore Prodotto');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prodotto $prodotto): bool
    {
        return $user->checkPermissionTo('force-delete Prodotto');
    }
}
