<?php

namespace App\Mail\Identity;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $createdAt;
    public readonly string $unsubscribeUrl;

    public function __construct(
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly string $loginUrl,
        string $createdAt            = '',
        string $unsubscribeUrl       = '',
    ) {
        $this->createdAt      = $createdAt      ?: now()->translatedFormat('d F Y');
        $this->unsubscribeUrl = $unsubscribeUrl ?: config('app.url') . '/unsubscribe';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name'),
            ),
            replyTo: [
                new Address(
                    'support@' . parse_url(config('app.url'), PHP_URL_HOST),
                    config('app.name') . ' Support',
                ),
            ],
            subject: 'Selamat Datang di ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.examples.welcome',
            text: 'emails.examples.welcome.text',
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
