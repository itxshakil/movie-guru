<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class DatabaseApproachingMaxConnections extends Notification
{
    use Queueable;

    public function __construct(public string $connectionName, public int $connections)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable)
    {
        return new MailMessage()->markdown('emails.database-approaching-max-connections', [
            'connectionName' => $this->connectionName,
            'connections' => $this->connections,
        ]);
        // TODO: An email must have a "To", "Cc", or "Bcc" header.
        //        return new MailDatabaseApproachingMaxConnections($this->connectionName, $this->connections);
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray($notifiable): array
    {
        return [
            'connectionName' => $this->connectionName,
            'connections' => $this->connections,
        ];
    }
}
