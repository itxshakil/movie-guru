<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WatchModeSearchResult;
use App\DTOs\WatchModeSource;
use App\DTOs\WatchModeSourceMeta;
use App\Enums\WatchModeSearchField;
use App\Enums\WatchModeType;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class WatchModeService
{
    private string $baseUrl = 'https://api.watchmode.com/v1';

    /**
     * @var string[]
     */
    private array $apiKeys;

    public function __construct()
    {
        $this->apiKeys = explode(',', (string)config('services.watchmode.key'));
    }

    /**
     * Search titles or people.
     *
     * @param WatchModeType[] $types
     * @return WatchModeSearchResult[]
     */
    public function search(
        WatchModeSearchField $searchField,
        string $searchValue,
        array $types = [],
    ): array
    {
        $params = [
            'search_field' => $searchField->value,
            'search_value' => $searchValue,
        ];

        if ($types !== []) {
            $params['types'] = implode(',', array_map(fn($t) => $t->value, $types));
        }

        $raw = $this->request('/search/', $params);
        $results = [];

        foreach (($raw['title_results'] ?? []) as $title) {
            $results[] = WatchModeSearchResult::fromArray($title + ['type' => 'title']);
        }

        foreach (($raw['people_results'] ?? []) as $person) {
            $results[] = WatchModeSearchResult::fromArray($person + ['type' => 'person']);
        }

        return $results;
    }

    /**
     * Get streaming availability for a title, enriched with source metadata.
     *
     * @param array<string> $regions
     * @return Collection<array{availability: WatchModeSource, meta: WatchModeSourceMeta|null}>
     */
    public function getTitleSources(string|int $titleId, array $regions = []): Collection
    {
        $params = [];

        if ($regions !== []) {
            $params['regions'] = implode(',', $regions);
        }

        $raw = $this->request(sprintf('/title/%s/sources/', $titleId), $params);

        if (isset($raw['error'])) {
            Log::error('Error getting title sources', [
                'title_id' => $titleId,
                'regions' => $regions,
                'error' => $raw['error'],
                'raw' => $raw,
            ]);

            return collect();
        }

        try {
            $availability = collect($raw)->map(
                fn(array $item): WatchModeSource => WatchModeSource::fromArray($item),
            );
        } catch (Throwable $throwable) {
            Log::error('Error getting title sources', [
                'title_id' => $titleId,
                'regions' => $regions,
                'error' => $throwable->getMessage(),
                'raw' => $raw,
                'items' => collect($raw),
            ]);
            report($throwable);

            return collect();
        }

        // Attach cached metadata
        $meta = $this->getSources()->keyBy('id');

        return $availability->map(fn(WatchModeSource $source): array => [
            'availability' => $source,
            'meta' => $meta->get($source->sourceId),
        ]);
    }

    /**
     * Get all supported streaming sources (metadata)
     * Cached for 24 hours.
     *
     * @return Collection<WatchModeSourceMeta>
     */
    public function getSources(): Collection
    {
        return Cache::remember('watchmode.sources', now()->addDay(), function () {
            $raw = $this->request('/sources/');

            return collect($raw)->map(
                fn(array $item): WatchModeSourceMeta => WatchModeSourceMeta::fromArray($item),
            );
        });
    }

    /**
     * Get a random API key from the configured keys.
     *
     * @throws Exception|Throwable
     */
    private function getRandomApiKey(): string
    {
        throw_if($this->apiKeys === [], Exception::class, 'No API keys are configured.');

        return $this->apiKeys[array_rand($this->apiKeys)];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws Throwable
     */
    private function request(string $endpoint, array $params = []): array
    {
        $params['apiKey'] = $this->getRandomApiKey();

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(15)
                ->get($endpoint, $params)
                ->throw();

            return $response->json();
        } catch (RequestException $requestException) {
            $message = $requestException->getMessage();
            if (str_contains($message, 'Title not found')) {
                return ['error' => 'Title not found'];
            }

            report($requestException);

            return ['error' => $requestException->getMessage()];
        }
    }
}
