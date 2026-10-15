<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MovieDetail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Looks up a title's details: from the database when we have it (refreshing it from OMDb in the
 * background), otherwise from OMDb, saving the result after the response is sent.
 */
final readonly class MovieDetailService
{
    public function __construct(
        private OMDBApiService $omdbApiService,
        private OmdbDetailMapper $omdbDetailMapper,
    ) {}

    /**
     * @return MovieDetail|array<string, mixed>|null
     */
    public function find(string $imdbId, string $ipAddress): MovieDetail|array|null
    {
        return Cache::flexible(
            'detail.' . $imdbId,
            [now()->addHours(18), now()->addHours(24)],
            fn (): MovieDetail|array|null => $this->load($imdbId, $ipAddress),
        );
    }

    /**
     * @return MovieDetail|array<string, mixed>|null
     */
    private function load(string $imdbId, string $ipAddress): MovieDetail|array|null
    {
        $movie = MovieDetail::query()->where('imdb_id', $imdbId)->first();

        if ($movie !== null) {
            defer(static fn () => $movie->incrementViews($ipAddress));
            defer(function () use ($imdbId): void {
                $this->refreshFromOmdb($imdbId);
            });

            return $movie;
        }

        $detail = $this->fetchFromOmdb($imdbId);

        defer(function () use ($detail, $imdbId, $ipAddress): void {
            if (! $this->isValidDetail($detail, $imdbId)) {
                return;
            }

            $this->save($imdbId, $detail)->incrementViews($ipAddress);
        });

        return $detail;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchFromOmdb(string $imdbId): ?array
    {
        $detail = $this->omdbApiService->getById($imdbId);

        return is_array($detail) ? $detail : null;
    }

    private function refreshFromOmdb(string $imdbId): void
    {
        $detail = $this->fetchFromOmdb($imdbId);

        if ($this->isValidDetail($detail, $imdbId)) {
            $this->save($imdbId, $detail);
        }
    }

    /**
     * @param array<string, mixed>|null $detail
     *
     * @phpstan-assert-if-true array<string, mixed> $detail
     */
    private function isValidDetail(?array $detail, string $imdbId): bool
    {
        if ($detail !== null && isset($detail['Title'])) {
            return true;
        }

        $response = is_array($detail) && is_string($detail['Response'] ?? null) ? $detail['Response'] : 'none';
        report(new Exception('Failed to fetch details for IMDB ID: ' . $imdbId . ' Response: ' . $response, 500));
        Log::error('Failed to fetch details for IMDB ID: ' . $imdbId, ['response' => $detail]);

        return false;
    }

    /**
     * @param array<string, mixed> $detail
     */
    private function save(string $imdbId, array $detail): MovieDetail
    {
        return MovieDetail::query()->updateOrCreate(
            ['imdb_id' => $imdbId],
            $this->omdbDetailMapper->toAttributes($detail),
        );
    }
}
