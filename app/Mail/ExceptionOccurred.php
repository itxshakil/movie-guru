<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class ExceptionOccurred extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('🔥 Production Error: ' . $this->data['message_short'])
            ->view('emails.error');
    }
}
