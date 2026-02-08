<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Services\TrendingQueryService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    public function index(TrendingQueryService $trendingQueryService): Response
    {
        $trendingSearchQueries = $trendingQueryService->fetch();
        $popularMovies = Cache::remember(
            'popular-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::popular()->take(6)->get([
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
            ]),
        );

        $trendingMovies = Cache::remember('trending-movies', now()->endOfDay(), static fn() => MovieDetail::trending()
            ->inRandomOrder()
            ->take(6)
            ->get([
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

        $hiddenGemsMovies = Cache::remember(
            'hidden-gems-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::hiddenGems()
                ->inRandomOrder()
                ->take(6)
                ->get([
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
                ]),
        );

        $recentlyReleasedMovies = Cache::remember(
            'recently-released-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::recentlyReleased()
                ->inRandomOrder()
                ->take(6)
                ->get([
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
                ]),
        );

        $recommendedMovies = Cache::remember(
            'recommended-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::recommended()
                ->inRandomOrder()
                ->take(6)
                ->get([
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
                ]),
        );

        $topRatedMovies = Cache::remember('top-rated-movies', now()->endOfDay(), static fn() => MovieDetail::topRated()
            ->inRandomOrder()
            ->take(6)
            ->get([
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

        return Inertia::render(
            'Welcome',
            [
                'trendingSearchQueries' => $trendingSearchQueries,
                'popularMovies' => $popularMovies,
                'trendingMovies' => $trendingMovies,
                'hiddenGemsMovies' => $hiddenGemsMovies,
                'recentlyReleasedMovies' => $recentlyReleasedMovies,
                'topRatedMovies' => $topRatedMovies,
                'recommendedMovies' => $recommendedMovies,
            ],
        );
    }
}
