<?php

namespace App\Notifications;

use App\Mail\DatabaseApproachingMaxConnections as MailDatabaseApproachingMaxConnections;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DatabaseApproachingMaxConnections extends Notification
{
    use Queueable;

    public function __construct(public string $connectionName, public int $connections)
    {}

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
        return (new MailMessage)->markdown('emails.database-approaching-max-connections', [
            'connectionName' => $this->connectionName,
            'connections' => $this->connections,
        ]);
        // TODO: An email must have a "To", "Cc", or "Bcc" header.
//        return new MailDatabaseApproachingMaxConnections($this->connectionName, $this->connections);
    }

    public function toArray($notifiable)
    {
        return [
            'connectionName' => $this->connectionName,
            'connections' => $this->connections
        ];
    }
}
