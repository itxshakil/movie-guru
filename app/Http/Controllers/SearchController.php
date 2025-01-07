<?php

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

        $defaultSearches = ['sholay', 'batman', 'spiderman','game of thrones', 'don', '3 idiots'];

        if(empty($search)){
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);

        $cacheKey = 'search-' . $search . '-' . $page . '-' . $movieType . '-' . $year;
        $movies = Cache::remember($cacheKey, now()->addHours(4), function () use ($OMDBApiService, $search, $page, $movieType, $year) {
            return $OMDBApiService->searchByTitle($search, $page, $movieType, $year);
        });

        $searchQuery->update([
            'response_at' => now(),
            'response' => $movies['Response'] === 'True',
            'response_result' => $movies
        ]);

        $movieTypes = MovieType::cases();

        $nextUrl = null;
        if($movies['Response'] === 'True'){
            $totalResults = $movies['totalResults'];
            $currentPage = $page;

            if($totalResults > ($currentPage * 10)){
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

        if($request->wantsJson()){
            return response()->json([
                'searchResults' => $movies,
                'search' => $search,
                'page' => $page,
                'movieType' => $movieType,
                'year' => $year,
                'movieTypes' => $movieTypes,
                'nextUrl' => $nextUrl,
                'trendingQueries' => $trendingQueries
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
            'trendingQueries' => $trendingQueries
        ]);

    }

    public function show(Request $request, string $imdbId, OMDBApiService $OMDBApiService){
        $detail = Cache::remember('detail.' . $imdbId, now()->addMinutes(120), function () use ($OMDBApiService, $imdbId) {
            return $OMDBApiService->getById($imdbId);
        });

        ShowPageAnalytics::create([
            'imdb_id' => $imdbId,
            'ip_address' => $request->ip(),
            'user_agent' =>  $request->userAgent()
        ]);

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
            'details' => $detail
        ])->incrementViews();

        if($request->wantsJson()){
            return response()->json(['detail' => $detail]);
        }

        return Inertia::render('Show', [
            'detail' => $detail
        ]);
    }

    /**
     * @param string|null $search
     * @param int|null $page
     * @param string|null $movieType
     * @param int|null $year
     * @param Request $request
     *
     * @return SearchQuery
     */
    public function logSearchQuery(?string $search, ?int $page, ?string $movieType, ?int $year, Request $request): SearchQuery
    {
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
