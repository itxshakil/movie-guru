<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WatchProvidersService
{
    /**
     * Fetch watch providers for a given IMDb ID using JustWatch.
     * Strategy:
     *  - Try to resolve title id via JustWatch search (by title/year), using OMDb as a fallback to get title/year from IMDb ID.
     *  - Fetch detailed offers and normalize into flatrate/rent/buy buckets.
     *  - Map provider ids to names and logos via the providers/locale endpoint.
     *
     * @param string $imdbId IMDb identifier (e.g., tt0111161)
     * @param string|null $type Optional: 'movie' or 'tv'. If null, attempt auto via OMDb type.
     * @param string $region ISO 3166-1 country code. Defaults to 'US'.
     * @return array Normalized payload with keys: region, link, flatrate, rent, buy, source
     */
    public function fetch(string $imdbId, ?string $type = null, string $region = 'US'): array
    {
        try {
            // Determine locale from region
            $locale = $this->regionToLocale($region);

            // Get title/year/type via OMDb as a reliable fallback for lookup
            $omdb = app(OMDBApiService::class);
            $omdbData = $omdb->getById($imdbId);
            if (!is_array($omdbData) || ($omdbData['Response'] ?? 'False') !== 'True') {
                return $this->emptyResult($region, 'omdb_lookup_failed');
            }

            $title = (string) ($omdbData['Title'] ?? '');
            $year = (int) (($omdbData['Year'] ?? 0) ?: 0);
            $resolvedType = $type ?? ($omdbData['Type'] ?? null);
            $jwType = $this->normalizeTypeForJW($resolvedType);

            if ($title === '' || $jwType === null) {
                return $this->emptyResult($region, 'insufficient_data');
            }

            // Step 1: Search JustWatch for the title to get JW ID
            $searchUrl = sprintf('https://apis.justwatch.com/content/titles/%s/popular', $locale);
            $searchBody = [
                'page_size' => 5,
                'page' => 1,
                'query' => $title,
                'content_types' => [$jwType],
            ];
            // Narrow by year if available
            if ($year > 0) {
                $searchBody['release_year_from'] = $year;
                $searchBody['release_year_until'] = $year;
            }

            $searchResp = Http::withHeaders($this->jwHeaders())
                ->retry(2, 150)
                ->post($searchUrl, $searchBody)
                ->json();

            $items = $searchResp['items'] ?? [];
            if (empty($items)) {
                return $this->emptyResult($region, 'jw_not_found');
            }

            // Prefer exact title+year match when possible
            $jwItem = $this->pickBestMatch($items, $title, $year);
            $jwId = $jwItem['id'] ?? null;
            $jwObjType = $jwItem['object_type'] ?? $jwType;
            if (!$jwId) {
                return $this->emptyResult($region, 'jw_no_id');
            }

            // Step 2: Fetch detail (offers)
            $detailUrl = sprintf('https://apis.justwatch.com/content/titles/%s/%s/locale/%s', $jwObjType, $jwId, $locale);
            $detail = Http::withHeaders($this->jwHeaders())
                ->retry(2, 150)
                ->get($detailUrl)
                ->json();

            $offers = $detail['offers'] ?? [];

            // Step 3: Build provider map for names/logos
            $providersMap = $this->fetchProvidersMap($locale);

            // Step 4: Normalize offers into buckets
            $normalized = $this->normalizeOffers($offers, $providersMap);
            $link = isset($detail['short_url']) ? $this->buildJustWatchUrl($detail['short_url']) : null;

            return [
                'region' => $region,
                'link' => $link,
                'flatrate' => $normalized['flatrate'],
                'rent' => $normalized['rent'],
                'buy' => $normalized['buy'],
                'source' => 'justwatch',
            ];
        } catch (\Throwable $e) {
            Log::warning('WatchProvidersService error: '.$e->getMessage(), ['imdbId' => $imdbId]);
            return $this->emptyResult($region, 'error');
        }
    }

    private function normalizeOffers(array $offers, array $providersMap): array
    {
        $result = [
            'flatrate' => [],
            'rent' => [],
            'buy' => [],
        ];

        foreach ($offers as $offer) {
            $monetization = $offer['monetization_type'] ?? null; // flatrate, rent, buy, ads, free, etc.
            $providerId = $offer['provider_id'] ?? null;
            if (!$monetization || !$providerId) {
                continue;
            }

            $provMeta = $providersMap[$providerId] ?? [
                'provider_name' => null,
                'logo_path' => null,
            ];

            $entry = [
                'provider_id' => $providerId,
                'provider_name' => $provMeta['provider_name'],
                'logo_path' => $provMeta['logo_path'],
            ];

            if (isset($result[$monetization])) {
                // prevent duplicates for same provider
                $already = array_filter($result[$monetization], fn($i) => ($i['provider_id'] ?? null) === $providerId);
                if (empty($already)) {
                    $result[$monetization][] = $entry;
                }
            }
        }

        // Sort providers alphabetically by name when available
        foreach (['flatrate','rent','buy'] as $k) {
            usort($result[$k], function ($a, $b) {
                return strcmp((string)($a['provider_name'] ?? ''), (string)($b['provider_name'] ?? ''));
            });
        }

        return $result;
    }

    private function fetchProvidersMap(string $locale): array
    {
        try {
            $url = sprintf('https://apis.justwatch.com/content/providers/locale/%s', $locale);
            $resp = Http::withHeaders($this->jwHeaders())
                ->retry(2, 150)
                ->get($url)
                ->json();

            $map = [];
            foreach ($resp as $prov) {
                $id = $prov['id'] ?? null;
                if ($id === null) { continue; }
                $name = $prov['clear_name'] ?? ($prov['short_name'] ?? ($prov['technical_name'] ?? null));
                $icon = $prov['icon_url'] ?? null;
                $logo = $icon ? $this->buildJustWatchImageUrl($icon) : null;
                $map[(int)$id] = [
                    'provider_name' => $name,
                    'logo_path' => $logo,
                ];
            }
            return $map;
        } catch (\Throwable $e) {
            Log::notice('Failed to fetch JW providers map: '.$e->getMessage());
            return [];
        }
    }

    private function regionToLocale(string $region): string
    {
        $region = strtoupper($region ?: 'US');
        // naive mapping: use English locale for the region
        return 'en_' . $region;
    }

    private function normalizeTypeForJW(?string $type): ?string
    {
        if (!$type) { return null; }
        $t = strtolower($type);
        return $t === 'tv' || $t === 'series' ? 'show' : ($t === 'movie' ? 'movie' : null);
    }

    private function pickBestMatch(array $items, string $title, int $year): array
    {
        $normalizedTitle = $this->normalizeTitle($title);
        $best = $items[0] ?? [];
        foreach ($items as $item) {
            $it = $this->normalizeTitle((string)($item['title'] ?? ''));
            $iy = (int)($item['original_release_year'] ?? 0);
            if ($it === $normalizedTitle && ($year === 0 || $iy === $year)) {
                return $item;
            }
        }
        return $best;
    }

    private function normalizeTitle(string $title): string
    {
        $t = strtolower($title);
        $t = preg_replace('/[^a-z0-9]+/', '', $t) ?? $t;
        return $t;
    }

    private function buildJustWatchUrl(string $shortUrl): string
    {
        $shortUrl = str_starts_with($shortUrl, '/') ? $shortUrl : '/'.$shortUrl;
        return 'https://www.justwatch.com'.$shortUrl;
    }

    private function buildJustWatchImageUrl(string $icon): string
    {
        // icon like /icon/12345/{profile}.png -> choose s100
        $icon = str_replace('{profile}', 's100', $icon);
        return 'https://images.justwatch.com'.$icon;
    }

    private function jwHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'movie-guru-bot/1.0 (+https://github.com/itxshakil/movie-guru)'
        ];
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
