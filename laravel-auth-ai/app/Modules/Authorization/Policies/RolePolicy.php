<?php

namespace App\Modules\Authorization\Policies;

use App\Modules\Authorization\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('roles.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.delete');
    }

    /**
     * Determine whether manage roles (generic permission).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }
}
