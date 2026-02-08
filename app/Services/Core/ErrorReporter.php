<?php

declare(strict_types=1);

namespace App\Services\Core;

use App\Mail\ExceptionOccurred;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
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

        Cache::put($key, true, now()->addMinutes((int)config('error_mail.rate_limit_minutes')));

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
            static fn(string $email) => filter_var($email, FILTER_VALIDATE_EMAIL),
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
        $request = request();

        return [
            'message_short' => class_basename($e),
            'message' => $e->getMessage(),
            'file' => basename($e->getFile() ?? ''),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => now()->toDateTimeString(),

            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'payload' => self::sanitizePayload($request->all()),
            'user' => auth()->check()
                ? [
                    'id' => auth()->id(),
                    'email' => auth()->user()?->email,
                ] : null,
            'environment' => app()->environment(),
            'request_id' => Context::get('request_id', 'NA'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }

    private static function sanitizePayload(array $payload): array
    {
        $fields = config('error_mail.sensitive_fields', []);

        foreach ($payload as $key => $value) {
            if (in_array($key, $fields, true)) {
                $payload[$key] = '****';
            } elseif (is_array($value)) {
                $payload[$key] = self::sanitizePayload($value);
            }
        }

        return $payload;
    }
}
