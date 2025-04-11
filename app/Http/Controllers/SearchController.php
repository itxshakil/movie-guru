<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Search;
use App\Models\SearchQuery;
use App\OMDB\MovieType;
use App\Services\OMDBApiService;
use App\Services\TrendingQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request, OMDBApiService $OMDBApiService, TrendingQueryService $trendingQueryService)
    {
        $search = $request->get('s');
        [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl] = $this->getSearchData(
            $search,
            $request
        );

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
        ]);
    }

    public function show(
        Request $request,
        string $search,
        OMDBApiService $OMDBApiService,
        TrendingQueryService $trendingQueryService
    ) {
        [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl] = $this->getSearchData(
            $search,
            $request
        );

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
        ]);
    }

    public function logSearchQuery(
        ?string $search,
        ?int $page,
        ?string $movieType,
        ?int $year,
        Request $request
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

    public function getSearchData(string $search, Request $request): array
    {
        $trendingQueryService = app(TrendingQueryService::class);
        $OMDBApiService = app(OMDBApiService::class);

        $page = $request->integer('page', 1);
        $movieType = $request->get('type');
        $year = $request->integer('year', null);

        $trendingQueries = $trendingQueryService->fetch();
        $defaultSearches = $trendingQueries->count() ? $trendingQueries->random(5)->toArray() : [];

        if (empty($search)) {
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);

        $cacheKey = 'searcgh-'.$search.'-'.$page.'-'.$movieType.'-'.$year;
        $movies = Cache::flexible(
            $cacheKey,
            [now()->addHours(16), now()->addHours(24)],
            function () use ($OMDBApiService, $search, $page, $movieType, $year) {
                return $OMDBApiService->searchByTitle($search, $page, $movieType, $year);
            }
        );

        defer(function () use ($search, $searchQuery, $movies) {
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

        $movieTypes = MovieType::cases();

        $nextUrl = null;
        if ($movies['Response'] === 'True') {
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
