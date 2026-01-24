<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\MovieDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param Collection<int, MovieDetail> $movies
     */
    public function __construct(
        public string       $type,
        public Collection   $movies,
        public string       $email,
        public ?MovieDetail $recommendedMovie = null,
        public ?MovieDetail $hiddenGem = null,
        public ?MovieDetail $trendingMovie = null,
        public ?string      $unsubscribeUrl = null,
    )
    {
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'weekly'
            ? '🎬 Your Weekly Movie Magic is Here!'
            : '🌟 Monthly Cinema Roundup: Don\'t Miss These!';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.weekly-monthly',
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
