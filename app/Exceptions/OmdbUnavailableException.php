<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * OMDb could not be reached or refused to answer (e.g. a Cloudflare challenge or origin overload).
 *
 * Rendered as a 503 so the failure is never cached or indexed as a real result.
 */
final class OmdbUnavailableException extends RuntimeException implements HttpExceptionInterface
{
    public static function fromHttpFailure(ConnectionException|RequestException $exception): self
    {
        $reason = $exception instanceof RequestException
            ? 'status ' . $exception->response->status()
            : 'connection failure';

        return new self('OMDb API is unavailable (' . $reason . ').', 0, $exception);
    }

    /**
     * Only transient failures are worth retrying; a 4xx such as a Cloudflare challenge will not clear up in milliseconds.
     */
    public static function isTransient(Throwable $exception): bool
    {
        return
            $exception instanceof ConnectionException
            || $exception instanceof RequestException
            && $exception->response->serverError();
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_SERVICE_UNAVAILABLE;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
