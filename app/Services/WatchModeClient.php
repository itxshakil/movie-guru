<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * HTTP client for the WatchMode API, spreading requests across the configured API keys.
 *
 * A key WatchMode turns away for quota or rate reasons rests for a while, so an exhausted plan
 * does not cost a failed request (and an error report) on every page view.
 */
final readonly class WatchModeClient
{
    public const int QUOTA_PAUSE_SECONDS = 3_600;

    public const int RATE_LIMIT_PAUSE_SECONDS = 60;

    private const string BASE_URL = 'https://api.watchmode.com/v1';

    /**
     * @var array<int, string>
     */
    private array $apiKeys;

    public function __construct()
    {
        // array_filter: an unset key would otherwise explode into [''] and be sent as an empty key.
        $this->apiKeys = array_values(array_filter(
            array_map(trim(...), explode(',', (string) config('services.watchmode.key'))),
            static fn (string $key): bool => $key !== '',
        ));
    }

    /**
     * Returns the decoded response, or ['error' => message] when the request fails or every key is resting.
     *
     * @param  array<string, mixed> $params
     * @return array<string, mixed>
     *
     * @throws Throwable when no API key is configured
     */
    public function get(string $endpoint, array $params = []): array
    {
        throw_if($this->apiKeys === [], Exception::class, 'No WatchMode API keys are configured.');

        $usableKeys = array_values(array_filter(
            $this->apiKeys,
            static fn (string $key): bool => ! Cache::has(self::pauseCacheKey($key)),
        ));

        if ($usableKeys === []) {
            return ['error' => 'Every WatchMode API key is over its quota'];
        }

        $apiKey = $usableKeys[array_rand($usableKeys)];
        $params['apiKey'] = $apiKey;

        try {
            $json = Http::baseUrl(self::BASE_URL)
                ->timeout(15)
                ->get($endpoint, $params)
                ->throw()
                ->json();

            if (! is_array($json)) {
                Log::warning('WatchMode returned a response that is not JSON.', ['endpoint' => $endpoint]);

                return ['error' => 'Unexpected WatchMode response'];
            }

            return $json;
        } catch (RequestException $requestException) {
            if (str_contains($requestException->getMessage(), 'Title not found')) {
                return ['error' => 'Title not found'];
            }

            if ($requestException->response->status() === Response::HTTP_TOO_MANY_REQUESTS) {
                $this->pause($apiKey, $requestException);

                return ['error' => 'WatchMode API key is over its quota'];
            }

            report($requestException);

            return ['error' => $requestException->getMessage()];
        }
    }

    private static function pauseCacheKey(string $apiKey): string
    {
        return 'watchmode.key-paused.' . hash('sha256', $apiKey);
    }

    private function pause(string $apiKey, RequestException $requestException): void
    {
        $isPlanQuota = str_contains($requestException->getMessage(), 'Over plan quota');
        $seconds = $isPlanQuota ? self::QUOTA_PAUSE_SECONDS : self::RATE_LIMIT_PAUSE_SECONDS;

        Cache::put(self::pauseCacheKey($apiKey), value: true, ttl: $seconds);

        Log::warning('A WatchMode API key was turned away; resting it before trying again.', [
            'key_index' => array_search($apiKey, $this->apiKeys, strict: true),
            'reason' => $isPlanQuota ? 'plan quota' : 'rate limit',
            'pause_seconds' => $seconds,
        ]);
    }
}
