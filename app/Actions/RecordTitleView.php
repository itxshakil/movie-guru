<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\MovieDetail;
use App\Models\ShowPageAnalytics;
use App\Services\WatchModeService;
use Illuminate\Support\Facades\Cache;

/**
 * Records a detail page view and, while at it, warms the trailer and refreshes streaming
 * sources that are missing or older than a month. Runs after the response is sent.
 */
final readonly class RecordTitleView
{
    public function __construct(
        private WatchModeService $watchModeService,
    ) {}

    public function handle(string $imdbId, ?string $ipAddress, ?string $userAgent, ?MovieDetail $movie): void
    {
        $shouldWarmTrailer = ! $this->isTrailerCached($imdbId);
        $shouldRefreshSources = $movie !== null && $this->hasStaleSources($movie);

        defer(function () use ($imdbId, $ipAddress, $userAgent, $shouldWarmTrailer, $shouldRefreshSources): void {
            ShowPageAnalytics::query()->create([
                'imdb_id' => $imdbId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            if ($shouldWarmTrailer) {
                $this->watchModeService->getTitleTrailer($imdbId);
            }

            if ($shouldRefreshSources) {
                $this->refreshSources($imdbId);
            }
        });
    }

    private function isTrailerCached(string $imdbId): bool
    {
        return Cache::has('watchmode.trailer.' . $imdbId);
    }

    private function hasStaleSources(MovieDetail $movie): bool
    {
        return ($movie->sources ?? []) === []
            || $movie->source_last_fetched_at === null
            || $movie->source_last_fetched_at->lt(now()->subMonth());
    }

    private function refreshSources(string $imdbId): void
    {
        $sources = $this->watchModeService->getTitleSources($imdbId, ['IN']);

        if ($sources->isEmpty()) {
            return;
        }

        MovieDetail::query()->where('imdb_id', $imdbId)->first()?->update([
            'sources' => $sources,
            'source_last_fetched_at' => now(),
        ]);
    }
}
