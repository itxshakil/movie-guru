<?php

declare(strict_types=1);

namespace App\Mail;

use App\DTOs\AdminDigestData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class AdminDigestMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly AdminDigestData $data,
    ) {}

    public function envelope(): Envelope
    {
        $label = $this->data->type === 'weekly' ? 'Weekly' : 'Monthly';
        $range = $this->data->periodStart->toDateString() . ' - ' . $this->data->periodEnd->toDateString();

        return new Envelope(
            subject: "{$label} Admin Digest - {$range}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.digest',
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
