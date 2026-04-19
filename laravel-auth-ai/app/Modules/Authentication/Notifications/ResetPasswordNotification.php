<?php

namespace App\Modules\Authentication\Notifications;

use App\Mail\Auth\ResetPasswordEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Request;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $email
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): ResetPasswordEmail
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ], false));

        return new ResetPasswordEmail(
            userName:  $notifiable->name,
            userEmail: $notifiable->email,
            actionUrl: $url,
            ipAddress: Request::ip() ?? '',
        );
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications-high',
        ];
    }
}
