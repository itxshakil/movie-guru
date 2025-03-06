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
        $page = $request->integer('page', 1);
        $movieType = $request->get('type');
        $year = $request->integer('year', null);

        $trendingQueries = $trendingQueryService->fetch();
        $defaultSearches = $trendingQueries->count() ? $trendingQueries->random(5)->toArray() : [];

        if (empty($search)) {
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);

        $cacheKey = 'search-'.$search.'-'.$page.'-'.$movieType.'-'.$year;
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

            Search::updateOrCreate([
                'query' => $search,
            ], [
                'total_results' => $movies['TotalResults'],
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
}
