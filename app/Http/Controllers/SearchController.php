<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\Search;
use App\Models\SearchQuery;
use App\OMDB\MovieType;
use App\Services\BotDetectorService;
use App\Services\OMDBApiService;
use App\Services\TitleCleaner;
use App\Services\TrendingQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

final class SearchController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $search = $request->get('s');
        [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl] = $this->getSearchData(
            $search,
            $request,
        );

        $trendingQueries = $trendingQueries->random(min(5, $trendingQueries->count()));

        $recentlyReleasedMovies = Cache::remember('recently-released-movies', now()->endOfDay(), fn() => MovieDetail::recentlyReleased()->inRandomOrder()->take(6)->get([
            'imdb_id',
            'title',
            'year',
            'release_date',
            'poster',
            'type',
            'imdb_rating',
            'imdb_votes',
            'director',
            'writer',
            'actors',
        ]));

        $recommendedMovies = Cache::remember('recommended-movies', now()->endOfDay(), fn() => MovieDetail::recommended()->inRandomOrder()->take(6)->get([
            'imdb_id',
            'title',
            'year',
            'release_date',
            'poster',
            'type',
            'imdb_rating',
            'imdb_votes',
            'director',
            'writer',
            'actors',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'searchResults' => $movies,
                'search' => $search,
                'page' => $page,
                'movieType' => $movieType,
                'year' => $year,
                'movieTypes' => $movieTypes,
                'nextUrl' => $nextUrl,
                'trendingQueries' => $trendingQueries,
            ]);
        }

        return Inertia::render('Search', [
            'searchResults' => $movies,
            'search' => $search,
            'page' => $page,
            'movieType' => $movieType,
            'year' => $year,
            'movieTypes' => $movieTypes,
            'nextUrl' => $nextUrl,
            'trendingQueries' => $trendingQueries,
            'recentlyReleasedMovies' => $recentlyReleasedMovies,
            'recommendedMovies' => $recommendedMovies,
        ]);
    }

    public function show(
        Request $request,
        ?string $search,
    ): Response|JsonResponse
    {
        [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl] = $this->getSearchData(
            $search,
            $request,
        );

        $trendingQueries = $trendingQueries->random(min(5, $trendingQueries->count()));

        $recentlyReleasedMovies = Cache::remember('recently-released-movies', now()->endOfDay(), fn() => MovieDetail::recentlyReleased()->inRandomOrder()->take(6)->get([
            'imdb_id',
            'title',
            'year',
            'release_date',
            'poster',
            'type',
            'imdb_rating',
            'imdb_votes',
            'director',
            'writer',
            'actors',
        ]));

        $recommendedMovies = Cache::remember('recommended-movies', now()->endOfDay(), fn() => MovieDetail::recommended()->inRandomOrder()->take(6)->get([
            'imdb_id',
            'title',
            'year',
            'release_date',
            'poster',
            'type',
            'imdb_rating',
            'imdb_votes',
            'director',
            'writer',
            'actors',
        ]));

        if ($request->wantsJson()) {
            return response()->json([
                'searchResults' => $movies,
                'search' => $search,
                'page' => $page,
                'movieType' => $movieType,
                'year' => $year,
                'movieTypes' => $movieTypes,
                'nextUrl' => $nextUrl,
                'trendingQueries' => $trendingQueries,
                'recentlyReleasedMovies' => $recentlyReleasedMovies,
                'recommendedMovies' => $recommendedMovies,
            ]);
        }

        return Inertia::render('Search', [
            'searchResults' => $movies,
            'search' => $search,
            'page' => $page,
            'movieType' => $movieType,
            'year' => $year,
            'movieTypes' => $movieTypes,
            'nextUrl' => $nextUrl,
            'trendingQueries' => $trendingQueries,
            'recentlyReleasedMovies' => $recentlyReleasedMovies,
            'recommendedMovies' => $recommendedMovies,
        ]);
    }

    public function logSearchQuery(
        ?string $search,
        ?int $page,
        ?string $movieType,
        ?int $year,
        Request $request,
    ): SearchQuery {
        return SearchQuery::create([
            'query' => $search ?? 'empty',
            'page' => $page,
            'type' => $movieType,
            'year' => $year,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * @return array{0: int, 1: string|null, 2: int|null, 3: Collection<int, string>, 4: string|null, 5: mixed, 6: array<int, MovieType>, 7: string|null}
     */
    public function getSearchData(?string $search, Request $request): array
    {
        $titleCleaner = resolve(TitleCleaner::class);
        $trendingQueryService = resolve(TrendingQueryService::class);
        $OMDBApiService = resolve(OMDBApiService::class);

        $page = $request->integer('page', 1);
        $movieType = $request->get('type');
        $year = $request->get('year') ? $request->integer('year') : null;

        $trendingQueries = $trendingQueryService->fetch();
        $defaultSearches = $trendingQueries->count() ? $trendingQueries->random(
            min(5, $trendingQueries->count()),
        )->all() : [];

        $search = $titleCleaner->clean($search);

        if (empty($search)) {
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $cacheKey = 'searcgh-' . $search . '-' . $page . '-' . $movieType . '-' . $year;
        $movies = Cache::flexible(
            $cacheKey,
            [now()->addHours(16), now()->addHours(24)],
            fn() => $OMDBApiService->searchByTitle($search, $page, $movieType, $year),
        );

        $botdetector = resolve(BotDetectorService::class);
        if ($botdetector->isBot($request) === false) {
            defer(function () use ($request, $year, $movieType, $page, $search, $movies): void {
                $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);
                $searchQuery->update([
                    'response_at' => now(),
                    'response' => $movies['Response'] === 'True',
                    'response_result' => $movies,
                ]);

                $totalResults = $movies['totalResults'] ?? 0;

                Search::updateOrCreate([
                    'query' => $search,
                ], [
                    'total_results' => $totalResults,
                ])->incrementViews();
            });
        }

        $movieTypes = MovieType::cases();

        $nextUrl = null;
        if (isset($movies['Response']) && $movies['Response'] === 'True') {
            $totalResults = $movies['totalResults'];
            $currentPage = $page;

            if ($totalResults > ($currentPage * 10)) {
                $nextPage = $currentPage + 1;
                $nextUrl = route('search', [
                    's' => $search,
                    'page' => $nextPage,
                    'type' => $movieType,
                    'year' => $year,
                ]);
            }
        }

        return [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl];
    }
}
