<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\UpdateWhereToWatch;
use App\Models\MovieDetail;
use Illuminate\Support\Facades\Log;

class WhereToWatchService
{
    public function __construct(private readonly WatchProvidersService $providers)
    {
    }

    /**
     * Get cached where-to-watch data if fresh; otherwise trigger background refresh.
     * If missing or stale, attempts a live fetch as fallback and persists.
     */
    public function get(string $imdbId, string $region = null, bool $forceRefresh = false): array
    {
        $region = $region ?: (string) config('watch.region', 'US');
        $ttlDays = (int) config('watch.ttl_days', 3);

        $movie = MovieDetail::where('imdb_id', $imdbId)->first();

        // Default structure
        $empty = [
            'region' => $region,
            'link' => null,
            'flatrate' => [],
            'rent' => [],
            'buy' => [],
            'source' => 'none',
            'fetched_at' => null,
            'expires_at' => null,
            'is_stale' => true,
        ];

        if (!$movie) {
            // No DB row yet -> try live fetch but don't require persistence here
            $data = $this->providers->fetch($imdbId, null, $region);
            $now = now();
            return array_merge($empty, $data, [
                'fetched_at' => $now->toISOString(),
                'expires_at' => $now->copy()->addDays($ttlDays)->toISOString(),
                'is_stale' => false,
            ]);
        }

        $isFresh = $movie->where_to_watch && $movie->where_to_watch_expires_at && now()->lt($movie->where_to_watch_expires_at);

        if ($isFresh && !$forceRefresh) {
            return [
                ...($movie->where_to_watch ?? []),
                'fetched_at' => optional($movie->where_to_watch_fetched_at)?->toISOString(),
                'expires_at' => optional($movie->where_to_watch_expires_at)?->toISOString(),
                'is_stale' => false,
            ];
        }

        // Trigger background refresh
        dispatch(new UpdateWhereToWatch($imdbId, $region));

        // Fallback: try live fetch immediately if missing or stale
        try {
            $data = $this->providers->fetch($imdbId, $this->detectType($movie), $region);
            $now = now();

            $movie->update([
                'where_to_watch' => $data,
                'where_to_watch_fetched_at' => $now,
                'where_to_watch_expires_at' => $now->copy()->addDays($ttlDays),
            ]);

            return array_merge($data, [
                'fetched_at' => $now->toISOString(),
                'expires_at' => $now->copy()->addDays($ttlDays)->toISOString(),
                'is_stale' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WhereToWatchService live fetch failed: '.$e->getMessage(), ['imdbId' => $imdbId]);

            // Return stale or empty data while background job will try to update
            if ($movie->where_to_watch) {
                return [
                    ...$movie->where_to_watch,
                    'fetched_at' => optional($movie->where_to_watch_fetched_at)?->toISOString(),
                    'expires_at' => optional($movie->where_to_watch_expires_at)?->toISOString(),
                    'is_stale' => true,
                ];
            }

            return $empty;
        }
    }

    private function detectType(?MovieDetail $movie): ?string
    {
        if (!$movie) {
            return null;
        }
        $t = strtolower((string) ($movie->type ?? ''));
        return in_array($t, ['movie', 'series']) ? ($t === 'series' ? 'tv' : 'movie') : null;
    }
}
