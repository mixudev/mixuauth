<?php

namespace App\Modules\Security\Policies;

use App\Modules\Security\Models\TrustedDevice;
use App\Models\User;

class TrustedDevicePolicy
{
    public function revoke(User $user, TrustedDevice $device): bool
    {
        return $user->isAdmin() || $device->user_id === $user->id;
    }

    public function view(User $user, TrustedDevice $device): bool
    {
        return $this->revoke($user, $device);
    }
}
