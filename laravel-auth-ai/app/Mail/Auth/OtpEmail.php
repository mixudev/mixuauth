<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $otpCode,
        public readonly int    $expiresMinutes = 5,
        public readonly string $ipAddress      = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name'),
            ),
            to: [
                new Address($this->userEmail, $this->userName),
            ],
            replyTo: [
                new Address(
                    'support@' . parse_url(config('app.url'), PHP_URL_HOST),
                    config('app.name') . ' Support',
                ),
            ],
            subject: 'Kode Verifikasi Login — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Mailer'   => config('app.name') . ' Mailer',
                'X-Priority' => '1',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
