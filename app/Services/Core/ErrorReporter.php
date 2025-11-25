<?php

declare(strict_types=1);

namespace App\Services\Core;

use App\Mail\ExceptionOccurred;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class ErrorReporter
{
    public static function report(Throwable $e): void
    {
        if (!config('error_mail.enabled')) {
            return;
        }

        $recipients = self::validRecipients();
        if ($recipients === []) {
            Log::error('No valid developer emails configured.');

            return;
        }

        $key = self::rateLimitKey($e);

        if (Cache::has($key)) {
            return; // Skip sending email (rate-limited)
        }

        Cache::put($key, true, now()->addMinutes(config('error_mail.rate_limit_minutes')));

        $data = self::buildPayload($e);

        try {
            Mail::to($recipients)->send(new ExceptionOccurred($data));
        } catch (Throwable $throwable) {
            Log::error('Failed to send exception email: ' . $throwable->getMessage());
        }
    }

    private static function validRecipients(): array
    {
        return array_values(array_filter(
            array_map(trim(...), config('error_mail.recipients')),
            fn(string $email) => filter_var($email, FILTER_VALIDATE_EMAIL),
        ));
    }

    private static function rateLimitKey(Throwable $e): string
    {
        if (config('error_mail.group_errors')) {
            return 'exception:' . md5($e->getMessage());
        }

        return 'exception:' . md5($e->getMessage() . microtime());
    }

    /**
     * @return array<string, mixed>
     */
    private static function buildPayload(Throwable $e): array
    {
        $file = pathinfo($e->getFile(), PATHINFO_BASENAME);

        $request = request();

        return [
            'message_short' => class_basename($e),
            'message' => $e->getMessage(),
            'file' => $file,
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now()->toDateTimeString(),

            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => self::sanitizePayload($request->all()),
            'user' => auth()->id(),
        ];
    }

    private static function sanitizePayload(array $payload): array
    {
        $fields = config('error_mail.sensitive_fields');

        foreach ($fields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '****';
            }
        }

        return $payload;
    }
}
