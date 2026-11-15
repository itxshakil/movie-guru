<?php

declare(strict_types=1);

namespace App\Services\Core;

use Illuminate\Cache\RateLimiter;
use Illuminate\Notifications\Notification;

use function Illuminate\Support\defer;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationSender;

use Throwable;

/**
 * Emails operational alerts (slow queries, a busy database) to the site admin.
 *
 * Each kind of alert is sent at most once per window, after the response, and a failed send is only
 * logged: an alert must never break the request that triggered it or flood the outgoing mail quota.
 */
final readonly class AdminAlerter
{
    public const int WINDOW_SECONDS = 900;

    public function __construct(
        private RateLimiter $rateLimiter,
    ) {}

    public function send(string $kind, Notification $notification): void
    {
        $address = (string) config('mail.admin.address');
        $key = 'admin-alert:' . $kind;

        if ($address === '' || $this->rateLimiter->tooManyAttempts($key, maxAttempts: 1)) {
            return;
        }

        // Counted before sending, so a mail server that keeps refusing is not retried on every request.
        $this->rateLimiter->hit($key, self::WINDOW_SECONDS);

        defer(static function () use ($address, $kind, $notification): void {
            try {
                NotificationSender::route('mail', $address)->notify($notification);
            } catch (Throwable $throwable) {
                Log::warning('Could not send the admin alert email.', [
                    'kind' => $kind,
                    'error' => $throwable->getMessage(),
                ]);
            }
        })->always();
    }
}
