<?php

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Services\TrendingQueryService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(TrendingQueryService $trendingQueryService)
    {
        $trendingSearchQueries = $trendingQueryService->fetch();

        $popularMovies = Cache::remember('popular-movies', now()->endOfDay(), function () {
            return MovieDetail::popular()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        $trendingMovies = Cache::remember('trending-movies', now()->endOfDay(), function () {
            return MovieDetail::trending()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        $hiddenGemsMovies = Cache::remember('hidden-gems-movies', now()->endOfDay(), function () {
            return MovieDetail::hiddenGems()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        $recentlyReleasedMovies = Cache::remember('recently-released-movies', now()->endOfDay(), function () {
            return MovieDetail::recentlyReleased()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        $recommendedMovies = Cache::remember('recommended-movies', now()->endOfDay(), function () {
            return MovieDetail::recommended()->inRandomOrder()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        $topRatedMovies = Cache::remember('top-rated-movies', now()->endOfDay(), function () {
            return MovieDetail::topRated()->take(6)->get([
                'imdb_id',
                'title',
                'year',
                'release_date',
                'poster',
                'type',
                'imdb_rating',
                'imdb_votes',
            ]);
        });

        return Inertia::render(
            'Welcome',
            compact(
                'trendingSearchQueries',
                'popularMovies',
                'trendingMovies',
                'hiddenGemsMovies',
                'recentlyReleasedMovies',
                'topRatedMovies',
                'recommendedMovies',
            )
        );
    }
}
