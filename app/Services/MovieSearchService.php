<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\SearchCriteria;
use App\Models\MovieDetail;
use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * Title search through OMDb, cached per query. Crawlers and bots reuse the cache and, once today's
 * bot budget is spent, get matches from the titles we store instead of a fresh OMDb call.
 */
final readonly class MovieSearchService
{
    public const int PER_PAGE = 10;

    private const int FALLBACK_TTL_HOURS = 6;

    public function __construct(
        private OMDBApiService $omdbApiService,
        private BotFetchBudget $botFetchBudget,
    ) {}

    /**
     * @throws Exception
     */
    public function search(SearchCriteria $criteria): mixed
    {
        return Cache::flexible(
            $criteria->cacheKey(),
            [now()->addHours(16), now()->addHours(24)],
            fn (): mixed => $this->omdbApiService->searchByTitle(
                $criteria->search,
                $criteria->page,
                $criteria->movieType,
                $criteria->year,
            ),
        );
    }

    /**
     * @throws Exception
     */
    public function searchForBot(SearchCriteria $criteria): mixed
    {
        $cached = Cache::get($criteria->cacheKey());

        if ($cached !== null) {
            return $cached;
        }

        if ($this->botFetchBudget->spend()) {
            return $this->search($criteria);
        }

        return Cache::remember(
            'bot-' . $criteria->cacheKey(),
            now()->addHours(self::FALLBACK_TTL_HOURS),
            fn (): array => $this->searchStoredTitles($criteria),
        );
    }

    /**
     * Matches from our own catalogue, in the shape OMDBApiService returns. Cached for bots only,
     * so people searching the same query still get the full OMDb results.
     *
     * @return array{Response: string, totalResults: string, Search: list<array<string, string>>, Error?: string}
     */
    private function searchStoredTitles(SearchCriteria $criteria): array
    {
        $titles = MovieDetail::query()
            ->where('title', 'like', '%' . $criteria->search . '%')
            ->when(filled($criteria->movieType), static fn ($query) => $query->where('type', $criteria->movieType))
            ->when($criteria->year !== null, static fn ($query) => $query->where('year', 'like', $criteria->year . '%'))
            ->popular()
            ->paginate(self::PER_PAGE, ['imdb_id', 'title', 'year', 'type', 'poster'], 'page', $criteria->page);

        if ($titles->total() === 0) {
            return OMDBApiService::NOT_FOUND;
        }

        return [
            'Response' => 'True',
            'totalResults' => (string) $titles->total(),
            'Search' => $titles->getCollection()->map(static fn (MovieDetail $movie): array => [
                'title' => $movie->title,
                'year' => $movie->year,
                'type' => $movie->type,
                'imdb_id' => $movie->imdb_id,
                'poster' => $movie->getRawOriginal('poster'),
            ])->all(),
        ];
    }
}
