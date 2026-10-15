<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MovieDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class TrendingMovieService
{
    /**
     * Today's trending picks, shared by the home page and the service worker's badge refresh.
     *
     * @return Collection<int, MovieDetail>
     */
    public function fetch(): Collection
    {
        return Cache::remember('trending-movies', now()->endOfDay(), static fn () => MovieDetail::trending()
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
    }
}
