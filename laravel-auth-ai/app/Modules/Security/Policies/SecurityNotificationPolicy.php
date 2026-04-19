<?php

namespace App\Modules\Security\Policies;

use App\Modules\Security\Models\SecurityNotification;
use App\Models\User;

class SecurityNotificationPolicy
{
    public function view(User $user, SecurityNotification $notification): bool
    {
        return $user->isAdmin() || $notification->user_id === $user->id;
    }

    public function update(User $user, SecurityNotification $notification): bool
    {
        return $this->view($user, $notification);
    }

    public function delete(User $user, SecurityNotification $notification): bool
    {
        return $this->view($user, $notification);
    }
}
