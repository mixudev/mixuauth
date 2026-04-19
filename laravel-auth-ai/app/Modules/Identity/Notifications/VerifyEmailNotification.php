<?php

namespace App\Modules\Identity\Notifications;

use App\Mail\Identity\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ?string $uuid  = null,
        public readonly ?string $token = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): VerifyEmail
    {
        $actionUrl = route('verification.verify', [
            'uuid'  => $this->uuid,
            'token' => $this->token,
        ]);

        return new VerifyEmail(
            userName:  $notifiable->name,
            userEmail: $notifiable->email,
            actionUrl: $actionUrl,
            expiresIn: '60 menit',
        );
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications-high',
        ];
    }
}
