<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class RequestLogger.
 *
 * This class is responsible for logging incoming requests and their responses.
 * It logs the request and response details such as IP, request ID, status, duration, user ID, body, and redirect URL.
 * It also logs the request method, URI, body, files, and referer.
 * The logging level is determined based on the response time.
 */
final class RequestLogger
{
    public const string X_REQUEST_ID = 'X-Request-ID';

    public const int RESPONSE_TIME_LIMIT = 1000;

    public const int WARNING_RESPONSE_TIME = 2000;

    public const int ERROR_RESPONSE_TIME = 10000;

    public float $startedAt;

    public int $requestId;

    private array $excludePaths = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $this->startedAt = $this->microtime_float();

        $this->requestId = $this->generateRequestId();

        Context::add('request_id', $this->requestId);
        $request->headers->set(self::X_REQUEST_ID, (string)$this->requestId);

        if ($this->shouldLogRequest($request)) {
            $this->logRequest($request);

            $response = $next($request);
            $response->headers->set(self::X_REQUEST_ID, (string)$this->requestId);

            $this->logResponse($response, $request);

            return $response;
        }

        return $next($request);
    }

    /**
     * Returns the current Unix timestamp with microseconds as a float value.
     *
     * @return float The current Unix timestamp with microseconds.
     */
    private function microtime_float(): float
    {
        [$usec, $sec] = explode(' ', microtime());

        return (float)$usec + (float)$sec;
    }

    /**
     * Generate a random request ID.
     */
    public function generateRequestId(): int
    {
        try {
            return random_int(100000, 999999);
        } catch (Exception $exception) {
            $message = 'Could not generate random request ID. Falling back to mt_rand().' . $exception->getMessage();
            $this->writeWarningMessage($message);

            return mt_rand(100000, 999999);
        }
    }

    /**
     * Write a warning message to the log.
     */
    private function writeWarningMessage(string $message): void
    {
        $this->logChannel()->warning($message);
    }

    /**
     * Get the logger instance for the 'request' channel.
     */
    private function logChannel(): LoggerInterface
    {
        return Log::channel('request');
    }

    /**
     * Determine if the request should be logged.
     */
    public function shouldLogRequest(Request $request): bool
    {
        return $this->inMethodsArray($request) && !$this->isFileRequest($request) && !$this->inExceptArray($request);
    }

    /**
     * Check if the request method is in the methods array.
     */
    private function inMethodsArray(Request $request): bool
    {
        $methods = collect(['GET', 'POST', 'PUT', 'DELETE']);

        return $methods->contains(mb_strtoupper($request->getMethod()));
    }

    /**
     * Check if the request is for a file.
     */
    public function isFileRequest(Request $request): bool
    {
        $fileExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif'];

        return in_array(pathinfo($request->getPathInfo(), PATHINFO_EXTENSION), $fileExtensions, true);
    }

    /**
     * Check if the request is in the except array.
     */
    private function inExceptArray(Request $request): bool
    {
        return array_any($this->excludePaths, fn(string $excludePath): bool => $this->isRequestInRoute($request, $excludePath));
    }

    /**
     * Check if the request is in the given route.
     */
    private function isRequestInRoute(Request $request, string $route): bool
    {
        if ($route !== '/') {
            $route = mb_trim($route, '/');
        }

        return $request->is($route);
    }

    /**
     * Log the request details.
     */
    private function logRequest(Request $request): void
    {
        $message = $this->generateLogMessage($request);
        $this->writeMessage($message);
    }

    private function generateLogMessage(Request $request): string
    {
        $method = mb_strtoupper($request->getMethod());
        $uri = $request->getPathInfo();

        $sensitiveFields = config('error_mail.sensitive_fields', ['password', 'password_confirmation']);
        $payload = $request->all();
        $this->sanitizeArray($payload, $sensitiveFields);

        $bodyAsJson = json_encode($payload) ?: '{}';
        $files = collect(iterator_to_array($request->files))
            ->map($this->flattenFiles(...))->flatten();

        return 'IP: ' . $request->ip() . ' #' . $this->requestId . sprintf(' %s %s - Body: %s (', $method, $uri, $bodyAsJson) . mb_strlen($bodyAsJson) . ') - Files: ' . $files->implode(',') . $this->referer($request);
    }

    private function sanitizeArray(array &$data, array $fields): void
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $fields, true)) {
                $value = '****';
            } elseif (is_array($value)) {
                $this->sanitizeArray($value, $fields);
            }
        }
    }

    /**
     * Get the referer from the request headers.
     */
    private function referer(Request $request): string
    {
        $referer = $request->headers->get('referer');
        if ($referer) {
            return ' - Referer: ' . $referer;
        }

        return '';
    }

    /**
     * Write a message to the log.
     */
    private function writeMessage(string $message): void
    {
        $this->logChannel()->info($message);
    }

    private function logResponse($response, Request $request): void
    {
        $duration = ($this->microtime_float() - $this->startedAt) * 1000;
        $message = $this->generateResponseLogMessage($response, $request, $duration);
        $this->logBasedOnResponseTime($duration, $message);
    }

    private function generateResponseLogMessage($response, Request $request, float $duration): string
    {
        $status = $response->getStatusCode();
        $redirect = '';
        $validationErrors = '';

        if ($status >= 300 && $status < 400) {
            $redirect = ' - Redirecting to ' . $response->getTargetUrl();

            if ($request->session()->has('errors')) {
                $errors = $request->session()->get('errors');

                $allErrors = $errors->all();

                if (!empty($allErrors)) {
                    $flatErrors = collect($allErrors)
                        ->flatten()
                        ->map(fn($e): string|false => is_scalar($e) ? (string)$e : json_encode($e))
                        ->all();

                    $validationErrors = ' - Validation Errors: ' . implode('; ', $flatErrors);
                }
            }
        }

        $authenticatable = $request->user();
        $authenticatableId = $authenticatable ? $authenticatable->id : 'Guest';

        $sensitiveFields = config('error_mail.sensitive_fields', ['password', 'password_confirmation']);
        $responseData = json_decode((string)$response->getContent(), true);

        if (is_array($responseData)) {
            $this->sanitizeArray($responseData, $sensitiveFields);
        }

        $bodyAsJson = $request->expectsJson()
            ? json_encode($responseData)
            : 'Non-JSON content returned';

        return 'IP: ' . $request->ip() . ' #' . $this->requestId .
            sprintf(' %s - Duration: %sms - UserId %s - Body: %s', $status, $duration, $authenticatableId, $bodyAsJson) .
            $redirect . $validationErrors;
    }

    /**
     * Log the message based on the response time.
     */
    private function logBasedOnResponseTime(float $duration, string $message): void
    {
        if ($duration < self::RESPONSE_TIME_LIMIT) {
            $this->writeMessage($message);
        } elseif ($duration < self::WARNING_RESPONSE_TIME) {
            $this->writeWarningMessage($message);
        } elseif ($duration < self::ERROR_RESPONSE_TIME) {
            $this->logChannel()->error($message);
        } else {
            $this->logChannel()->critical($message);
        }
    }

    /**
     * Flatten the files in the request.
     */
    public function flattenFiles(mixed $file): string|Collection
    {
        if ($file instanceof UploadedFile) {
            return $file->getClientOriginalName();
        }

        return collect($file)->map($this->flattenFiles(...));
    }
}
