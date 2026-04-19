<?php

namespace App\Modules\Authentication\Notifications;

use App\Mail\Auth\OtpEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Request;

class OtpCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $otpCode
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): OtpEmail
    {
        return new OtpEmail(
            userName:       $notifiable->name,
            userEmail:      $notifiable->email,
            otpCode:        $this->otpCode,
            expiresMinutes: config('security.otp.expires_minutes', 5),
            ipAddress:      Request::ip() ?? '',
        );
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'notifications-high',
        ];
    }
}
