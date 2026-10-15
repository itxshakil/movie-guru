<?php

declare(strict_types=1);

namespace App\Support\RequestLogging;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds the request log lines, masking sensitive fields.
 */
final class RequestLogFormatter
{
    public function formatRequest(Request $request, int $requestId): string
    {
        $bodyAsJson = json_encode($this->sanitize($request->all())) ?: '{}';
        $files = collect(iterator_to_array($request->files))->map($this->flattenFiles(...))->flatten();
        $referer = $request->headers->get('referer');

        return sprintf(
            'IP: %s #%d %s %s - Body: %s (%d) - Files: %s%s',
            $request->ip(),
            $requestId,
            mb_strtoupper($request->getMethod()),
            $request->getPathInfo(),
            $bodyAsJson,
            mb_strlen($bodyAsJson),
            $files->implode(','),
            $referer ? ' - Referer: ' . $referer : '',
        );
    }

    public function formatResponse(Response $response, Request $request, int $requestId, float $durationMs): string
    {
        $responseData = json_decode((string) $response->getContent(), associative: true);
        $bodyAsJson = $request->expectsJson()
            ? json_encode(is_array($responseData) ? $this->sanitize($responseData) : $responseData)
            : 'Non-JSON content returned';

        return sprintf(
            'IP: %s #%d %d - Duration: %sms - UserId %s - Body: %s%s',
            $request->ip(),
            $requestId,
            $response->getStatusCode(),
            $durationMs,
            $request->user()->id ?? 'Guest',
            $bodyAsJson,
            $this->redirectDetails($response, $request),
        );
    }

    /**
     * Where a redirect goes and, for failed form submissions, which fields failed validation.
     */
    private function redirectDetails(Response $response, Request $request): string
    {
        if (! $response instanceof RedirectResponse) {
            return '';
        }

        $details = ' - Redirecting to ' . $response->getTargetUrl();

        $errors = $request->hasSession() ? $request->session()->get('errors') : null;
        $messages = $errors?->all() ?? [];
        if ($messages === []) {
            return $details;
        }

        $flatMessages = collect($messages)
            ->flatten()
            ->map(static fn (mixed $message): string => is_scalar($message) ? (string) $message : (string) json_encode($message));

        return $details . ' - Validation Errors: ' . $flatMessages->implode('; ');
    }

    /**
     * @param  array<array-key, mixed> $data
     * @return array<array-key, mixed>
     */
    private function sanitize(array $data): array
    {
        /** @var array<int, string> $sensitiveFields */
        $sensitiveFields = config('error_mail.sensitive_fields', ['password', 'password_confirmation']);

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveFields, strict: true)) {
                $data[$key] = '****';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }

    /**
     * @return string|Collection<array-key, mixed>
     */
    private function flattenFiles(mixed $file): string|Collection
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();
        }

        return collect($file)->map($this->flattenFiles(...));
    }
}
