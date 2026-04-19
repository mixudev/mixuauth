<?php

namespace App\Modules\Authorization\Policies;

use App\Modules\Authorization\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permissions.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('permissions.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permissions.delete');
    }

    /**
     * Determine whether manage permissions (generic permission).
     */
    public function manage(User $user): bool
    {
        return $user->hasPermission('permissions.view');
    }
}
