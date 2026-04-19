<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $unsubscribeUrl;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $actionUrl,
        public readonly string $expiresIn = '60 menit',
        public readonly string $ipAddress = '',
        string $unsubscribeUrl            = '',
    ) {
        $this->unsubscribeUrl = $unsubscribeUrl ?: config('app.url') . '/unsubscribe';
    }

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
            subject: 'Permintaan Reset Password — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.examples.reset-password',
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Mailer'              => config('app.name') . ' Mailer',
                'X-Priority'            => '3',
                'List-Unsubscribe'      => '<' . $this->unsubscribeUrl . '>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
