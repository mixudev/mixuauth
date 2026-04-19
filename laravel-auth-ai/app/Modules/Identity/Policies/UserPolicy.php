<?php

namespace App\Modules\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function manage(User $user): bool
    {
        return false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $model): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return false;
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }
}
