<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\WatchModeSearchResult;
use App\DTOs\WatchModeSource;
use App\DTOs\WatchModeSourceMeta;
use App\Enums\WatchModeSearchField;
use App\Enums\WatchModeType;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WatchModeService
{
    protected string $apiKey;

    protected string $baseUrl = 'https://api.watchmode.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.watchmode.key');
    }

    /**
     * Search titles or people
     *
     * @param WatchModeType[] $types
     * @return WatchModeSearchResult[]
     */
    public function search(
        WatchModeSearchField $searchField,
        string $searchValue,
        array $types = []
    ): array
    {
        $params = [
            'search_field' => $searchField->value,
            'search_value' => $searchValue,
        ];

        if (!empty($types)) {
            $params['types'] = implode(',', array_map(fn($t) => $t->value, $types));
        }

        $raw = $this->request('/search/', $params);

        dd($raw);
        $results = [];

        foreach (($raw['title_results'] ?? []) as $title) {
            $results[] = WatchModeSearchResult::fromArray($title + ['type' => 'title']);
        }

        foreach (($raw['people_results'] ?? []) as $person) {
            $results[] = WatchModeSearchResult::fromArray($person + ['type' => 'person']);
        }

        return $results;
    }

    protected function request(string $endpoint, array $params = []): array
    {
        $params['apiKey'] = $this->apiKey;

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(15)
                ->get($endpoint, $params)
                ->throw();

            return $response->json();
        } catch (RequestException $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'Title not found')) {
                return ['error' => 'Title not found'];
            }
            report($e);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get streaming availability for a title, enriched with source metadata
     *
     * @param array<string> $regions
     * @return Collection<array{availability: WatchModeSource, meta: WatchModeSourceMeta|null}>
     */
    public function getTitleSources(string|int $titleId, array $regions = []): Collection
    {
        $params = [];

        if (!empty($regions)) {
            $params['regions'] = implode(',', $regions);
        }

        $raw = $this->request("/title/$titleId/sources/", $params);

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
                fn($item) => WatchModeSource::fromArray($item)
            );
        } catch (Throwable $e) {
            Log::error('Error getting title sources', [
                'title_id' => $titleId,
                'regions' => $regions,
                'error' => $e->getMessage(),
                'raw' => $raw,
                'items' => collect($raw),
            ]);
            report($e);

            return collect();
        }

        // Attach cached metadata
        $meta = $this->getSources()->keyBy('id');

        return $availability->map(fn(WatchModeSource $source) => [
            'availability' => $source,
            'meta' => $meta->get($source->sourceId),
        ]);
    }

    /**
     * Get all supported streaming sources (metadata)
     * Cached for 24 hours
     *
     * @return Collection<WatchModeSourceMeta>
     */
    public function getSources(): Collection
    {
        return Cache::remember('watchmode.sources', now()->addDay(), function () {
            $raw = $this->request('/sources/');

            return collect($raw)->map(
                fn($item) => WatchModeSourceMeta::fromArray($item)
            );
        });
    }
}
