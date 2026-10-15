<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\RequestLogging\RequestLogFilter;
use App\Support\RequestLogging\RequestLogFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs incoming requests and their responses to the "request" channel, tagged with a request ID.
 * The log level follows the response time.
 */
final readonly class RequestLogger
{
    public const string X_REQUEST_ID = 'X-Request-ID';

    public const int RESPONSE_TIME_LIMIT = 1000;

    public const int WARNING_RESPONSE_TIME = 2000;

    public const int ERROR_RESPONSE_TIME = 10_000;

    public function __construct(
        private RequestLogFilter $requestLogFilter,
        private RequestLogFormatter $requestLogFormatter,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $startedAt = hrtime(true);
        $requestId = random_int(100_000, 999_999);

        Context::add('request_id', $requestId);
        $request->headers->set(self::X_REQUEST_ID, (string) $requestId);

        if (! $this->requestLogFilter->shouldLog($request)) {
            return $next($request);
        }

        Log::channel('request')->info($this->requestLogFormatter->formatRequest($request, $requestId));

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set(self::X_REQUEST_ID, (string) $requestId);

        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $message = $this->requestLogFormatter->formatResponse($response, $request, $requestId, $durationMs);
        Log::channel('request')->log($this->levelFor($durationMs), $message);

        return $response;
    }

    private function levelFor(float $durationMs): string
    {
        return match (true) {
            $durationMs < self::RESPONSE_TIME_LIMIT => 'info',
            $durationMs < self::WARNING_RESPONSE_TIME => 'warning',
            $durationMs < self::ERROR_RESPONSE_TIME => 'error',
            default => 'critical',
        };
    }
}
