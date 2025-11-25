<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SlowQueryDetected extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Connection $queryConnection, public QueryExecuted $event)
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

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $timeInSeconds = $this->event->time / 1000;

        return (new MailMessage())->markdown('emails.slow-query-detected', [
            'connectionName' => $this->queryConnection->getName(),
            'query' => $this->finalQuery(),
            'time' => $timeInSeconds,
        ]);
        // TODO: This gives error
        //        return new MailSlowQueryDetected($this->queryConnection->getName(), $this->finalQuery(), $timeInSeconds);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'connectionName' => $this->queryConnection->getName(),
            'query' => $this->finalQuery(),
            'time' => $this->event->time,
            'timeInSeconds' => $this->event->time / 1000,
        ];
    }

    private function finalQuery(): string
    {
        $query = $this->event->sql;

        foreach ($this->event->bindings as $binding) {
            $query = preg_replace('/\?/', sprintf("'%s'", $binding), (string)$query, 1);
        }

        return $query;
    }
}
