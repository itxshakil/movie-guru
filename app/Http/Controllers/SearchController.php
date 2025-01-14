<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\SearchQuery;
use App\Models\ShowPageAnalytics;
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
        $page = $request->get('page', 1);
        $movieType = $request->get('type');
        $year = $request->get('year');

        $defaultSearches = ['sholay', 'batman', 'spiderman', 'game of thrones', 'don', '3 idiots'];

        if (empty($search)) {
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);

        $cacheKey = 'search-'.$search.'-'.$page.'-'.$movieType.'-'.$year;
        $movies = Cache::remember(
            $cacheKey,
            now()->addHours(4),
            function () use ($OMDBApiService, $search, $page, $movieType, $year) {
                return $OMDBApiService->searchByTitle($search, $page, $movieType, $year);
            }
        );

        defer(function () use ($searchQuery, $movies) {
            $searchQuery->update([
                'response_at' => now(),
                'response' => $movies['Response'] === 'True',
                'response_result' => $movies,
            ]);
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

        $trendingQueries = $trendingQueryService->fetch();

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

    public function show(Request $request, string $imdbId, OMDBApiService $OMDBApiService)
    {
        $cacheKey = 'detail.'.$imdbId;
        $detail = Cache::remember($cacheKey, now()->addHours(6), function () use ($imdbId, $OMDBApiService) {
            // Check for the movie in the database
            $movie = MovieDetail::where('imdb_id', $imdbId)->first();

            if ($movie) {
                defer(fn() => $movie->incrementViews());
                $this->updateMovieDetailsInBackground($imdbId);

                return $movie->details;
            }

            // If not found in the database, fetch from the API
            $detail = $OMDBApiService->getById($imdbId);

            defer(function () use ($detail, $imdbId) {
                // Save the details to the database
                MovieDetail::updateOrCreate([
                    'imdb_id' => $imdbId,
                ], [
                    'title' => $detail['Title'],
                    'year' => $detail['Year'],
                    'release_date' => $detail['Released'],
                    'poster' => $detail['Poster'],
                    'type' => $detail['Type'],
                    'imdb_rating' => $detail['imdbRating'],
                    'imdb_votes' => str_replace(',', '', $detail['imdbVotes']),
                    'details' => $detail,
                ])->incrementViews();
            });

            return $detail; // Return fetched details
        });

        defer(function () use ($request, $imdbId) {
            // Log the analytics
            ShowPageAnalytics::create([
                'imdb_id' => $imdbId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json(['detail' => $detail]);
        }

        return Inertia::render('Show', [
            'detail' => $detail,
        ]);
    }

    private function updateMovieDetailsInBackground(string $imdbId): void
    {
        defer(function () use ($imdbId) {
            $OMDBApiService = app(OMDBApiService::class);
            $updatedDetail = $OMDBApiService->getById($imdbId);

            MovieDetail::updateOrCreate([
                'imdb_id' => $imdbId,
            ], [
                'title' => $updatedDetail['Title'],
                'year' => $updatedDetail['Year'],
                'release_date' => $updatedDetail['Released'],
                'poster' => $updatedDetail['Poster'],
                'type' => $updatedDetail['Type'],
                'imdb_rating' => $updatedDetail['imdbRating'],
                'imdb_votes' => str_replace(',', '', $updatedDetail['imdbVotes']),
                'details' => $updatedDetail,
            ]);
        });
    }
}
