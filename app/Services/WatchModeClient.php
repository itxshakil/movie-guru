<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * HTTP client for the WatchMode API, spreading requests across the configured API keys.
 */
final readonly class WatchModeClient
{
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
     * Returns the decoded response, or ['error' => message] when the request fails.
     *
     * @param  array<string, mixed> $params
     * @return array<string, mixed>
     *
     * @throws Throwable when no API key is configured
     */
    public function get(string $endpoint, array $params = []): array
    {
        throw_if($this->apiKeys === [], Exception::class, 'No WatchMode API keys are configured.');

        $params['apiKey'] = $this->apiKeys[array_rand($this->apiKeys)];

        try {
            $json = Http::baseUrl(self::BASE_URL)
                ->timeout(15)
                ->get($endpoint, $params)
                ->throw()
                ->json();

            return is_array($json) ? $json : ['error' => 'Unexpected WatchMode response'];
        } catch (RequestException $requestException) {
            if (str_contains($requestException->getMessage(), 'Title not found')) {
                return ['error' => 'Title not found'];
            }

            report($requestException);

            return ['error' => $requestException->getMessage()];
        }
    }
}
