<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class WelcomeSubscriberMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $firstName,
        public readonly string $email,
    )
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎬 Welcome to Movie Guru — Your Cinematic Journey Begins!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.welcome',
        );
    }
}
