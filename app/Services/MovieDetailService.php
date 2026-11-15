<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BotFetchBudgetExhaustedException;
use App\Models\MovieDetail;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Looks up a title's details: from the database when we have it (refreshing it from OMDb in the
 * background at most once a week), otherwise from OMDb, saving the result after the response is sent.
 */
final readonly class MovieDetailService
{
    private const string CACHE_PREFIX = 'detail.';

    public function __construct(
        private OMDBApiService $omdbApiService,
        private OmdbDetailMapper $omdbDetailMapper,
        private BotFetchBudget $botFetchBudget,
    ) {}

    /**
     * @return MovieDetail|array<string, mixed>|null
     */
    public function find(string $imdbId, string $ipAddress): MovieDetail|array|null
    {
        return $this->remember($imdbId, fn (): MovieDetail|array|null => $this->load($imdbId, $ipAddress));
    }

    /**
     * The same lookup for crawlers and bots, which only reach OMDb for titles we know nothing about,
     * and only while today's bot budget lasts. They never count as views or trigger a refresh.
     *
     * @return MovieDetail|array<string, mixed>|null
     *
     * @throws BotFetchBudgetExhaustedException
     */
    public function findForBot(string $imdbId): MovieDetail|array|null
    {
        $cached = Cache::get(self::CACHE_PREFIX . $imdbId)
            ?? MovieDetail::query()->where('imdb_id', $imdbId)->first();

        if ($cached !== null) {
            return $cached;
        }

        if (! $this->botFetchBudget->spend()) {
            throw BotFetchBudgetExhaustedException::retryAfter($this->botFetchBudget->secondsUntilRefill());
        }

        return $this->remember($imdbId, fn (): ?array => $this->fetchAndStore($imdbId, ipAddress: null));
    }

    /**
     * @param  callable(): (MovieDetail|array<string, mixed>|null) $loader
     * @return MovieDetail|array<string, mixed>|null
     */
    private function remember(string $imdbId, callable $loader): MovieDetail|array|null
    {
        return Cache::flexible(self::CACHE_PREFIX . $imdbId, [now()->addHours(18), now()->addHours(24)], $loader);
    }

    /**
     * @return MovieDetail|array<string, mixed>|null
     */
    private function load(string $imdbId, string $ipAddress): MovieDetail|array|null
    {
        $movie = MovieDetail::query()->where('imdb_id', $imdbId)->first();

        if ($movie === null) {
            return $this->fetchAndStore($imdbId, $ipAddress);
        }

        defer(static fn () => $movie->incrementViews($ipAddress));

        $isRefreshDue = $movie->omdb_last_fetched_at === null || $movie->omdb_last_fetched_at->lt(now()->subWeek());

        if ($isRefreshDue) {
            defer(function () use ($imdbId): void {
                $this->refreshFromOmdb($imdbId);
            });
        }

        return $movie;
    }

    /**
     * Fetches a title we do not store yet and saves it after the response is sent.
     *
     * @return array<string, mixed>|null
     */
    private function fetchAndStore(string $imdbId, ?string $ipAddress): ?array
    {
        $detail = $this->fetchFromOmdb($imdbId);

        defer(function () use ($detail, $imdbId, $ipAddress): void {
            if (! $this->isValidDetail($detail, $imdbId)) {
                return;
            }

            $movie = $this->save($imdbId, $detail);

            if ($ipAddress !== null) {
                $movie->incrementViews($ipAddress);
            }
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
            [...$this->omdbDetailMapper->toAttributes($detail), 'omdb_last_fetched_at' => now()],
        );
    }
}
