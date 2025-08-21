<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatchProvidersService
{
    /**
     * Fetch watch providers for a given IMDb ID using TMDB if configured.
     * Fallback: returns an empty result gracefully if TMDB_API_KEY is not set or any error occurs.
     *
     * @param string $imdbId IMDb identifier (e.g., tt0111161)
     * @param string|null $type Optional: 'movie' or 'tv'. If null, auto-detects via TMDB find endpoint.
     * @param string $region ISO 3166-1 country code. Defaults to 'US'.
     * @return array Normalized payload with keys: region, link, flatrate, rent, buy
     */
    public function fetch(string $imdbId, ?string $type = null, string $region = 'US'): array
    {
        $apiKey = config('services.tmdb.api_key');
        if (empty($apiKey)) {
            return $this->emptyResult($region, 'no_api_key');
        }

        try {
            // Step 1: Find TMDB ID by IMDb ID
            $findUrl = 'https://api.themoviedb.org/3/find/' . urlencode($imdbId);
            $findResp = Http::retry(1, 100)->get($findUrl, [
                'api_key' => $apiKey,
                'external_source' => 'imdb_id',
            ])->json();

            $tmdbType = $type;
            $tmdbId = null;

            if (!$tmdbType) {
                if (!empty($findResp['movie_results'])) {
                    $tmdbType = 'movie';
                    $tmdbId = $findResp['movie_results'][0]['id'] ?? null;
                } elseif (!empty($findResp['tv_results'])) {
                    $tmdbType = 'tv';
                    $tmdbId = $findResp['tv_results'][0]['id'] ?? null;
                }
            } else {
                $key = $tmdbType === 'tv' ? 'tv_results' : 'movie_results';
                if (!empty($findResp[$key])) {
                    $tmdbId = $findResp[$key][0]['id'] ?? null;
                }
            }

            if (!$tmdbType || !$tmdbId) {
                return $this->emptyResult($region, 'not_found');
            }

            // Step 2: Fetch watch providers from TMDB
            $provUrl = sprintf('https://api.themoviedb.org/3/%s/%d/watch/providers', $tmdbType, $tmdbId);
            $provResp = Http::retry(1, 100)->get($provUrl, ['api_key' => $apiKey])->json();
            $results = $provResp['results'] ?? [];
            $entry = $results[$region] ?? [];

            return [
                'region' => $region,
                'link' => $entry['link'] ?? null,
                'flatrate' => $this->normalizeProviders($entry['flatrate'] ?? []),
                'rent' => $this->normalizeProviders($entry['rent'] ?? []),
                'buy' => $this->normalizeProviders($entry['buy'] ?? []),
                'source' => 'tmdb',
            ];
        } catch (\Throwable $e) {
            Log::warning('WatchProvidersService error: '.$e->getMessage(), ['imdbId' => $imdbId]);
            return $this->emptyResult($region, 'error');
        }
    }

    private function normalizeProviders(array $items): array
    {
        return array_map(function ($item) {
            return [
                'provider_id' => $item['provider_id'] ?? null,
                'provider_name' => $item['provider_name'] ?? null,
                'logo_path' => isset($item['logo_path']) ? 'https://image.tmdb.org/t/p/w92'.$item['logo_path'] : null,
            ];
        }, $items);
    }

    private function emptyResult(string $region, string $reason): array
    {
        return [
            'region' => $region,
            'link' => null,
            'flatrate' => [],
            'rent' => [],
            'buy' => [],
            'source' => $reason,
        ];
    }
}
