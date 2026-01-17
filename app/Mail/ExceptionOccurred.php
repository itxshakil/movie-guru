<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ExceptionOccurred extends Mailable
{
    use Queueable;

    use SerializesModels;

    public string $environment;

    public function __construct(public array $data)
    {
        $this->environment = $this->data['environment'] ?? app()->environment();
    }

    public function envelope(): Envelope
    {
        $app = config('app.name');

        return new Envelope(
            subject: sprintf('%s (%s) Exception: %s', $app, $this->environment, $this->data['message_short']),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.exception-occurred',
        );
    }

    /**
     * @return array{}
     */
    public function attachments(): array
    {
        return [];
    }
}
