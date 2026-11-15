<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * A bot asked for something we would have to fetch from OMDb after today's bot budget ran out.
 *
 * Rendered as a 503 with Retry-After, which tells well-behaved crawlers to come back later without dropping the page.
 */
final class BotFetchBudgetExhaustedException extends ServiceUnavailableHttpException
{
    public static function retryAfter(int $seconds): self
    {
        return new self($seconds, 'The daily OMDb budget for bots is used up.');
    }
}
