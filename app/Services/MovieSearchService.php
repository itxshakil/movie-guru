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
    private const int PER_PAGE = 10;

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

        return $this->searchStoredTitles($criteria);
    }

    /**
     * Matches from our own catalogue, in the shape OMDBApiService returns. Deliberately not cached,
     * so people searching the same query still get the full OMDb results.
     *
     * @return array{Response: string, totalResults: string, Search: list<array<string, string>>, Error?: string}
     */
    private function searchStoredTitles(SearchCriteria $criteria): array
    {
        $titles = MovieDetail::query()
            ->where('title', 'like', '%' . $criteria->search . '%')
            ->when(
                $criteria->movieType !== null && $criteria->movieType !== '',
                static fn ($query) => $query->where('type', $criteria->movieType),
            )
            ->when($criteria->year !== null, static fn ($query) => $query->where('year', 'like', $criteria->year . '%'))
            ->orderByDesc('views')
            ->paginate(self::PER_PAGE, ['imdb_id', 'title', 'year', 'type', 'poster'], 'page', $criteria->page);

        if ($titles->total() === 0) {
            return ['Response' => 'False', 'Error' => 'Movie not found!', 'Search' => [], 'totalResults' => '0'];
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
            ])->values()->all(),
        ];
    }
}
