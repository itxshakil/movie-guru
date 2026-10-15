<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MovieDetail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The cached movie rows shown on the home, search and detail pages.
 */
final class MovieCollectionService
{
    /**
     * Columns the movie cards need.
     */
    public const array CARD_COLUMNS = [
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
    ];

    /**
     * @return Collection<int, MovieDetail>
     */
    public function recentlyReleased(): Collection
    {
        return Cache::remember('recently-released-movies', now()->endOfDay(), static fn () => MovieDetail::recentlyReleased()
            ->inRandomOrder()
            ->take(6)
            ->get(self::CARD_COLUMNS));
    }

    /**
     * @return Collection<int, MovieDetail>
     */
    public function recommended(): Collection
    {
        return Cache::remember('recommended-movies', now()->endOfDay(), static fn () => MovieDetail::recommended()
            ->inRandomOrder()
            ->take(6)
            ->get(self::CARD_COLUMNS));
    }

    /**
     * The best-rated titles sharing the first genre of the given genre list.
     *
     * @return Collection<int, MovieDetail>
     */
    public function similarTo(string $genres, string $excludedImdbId): Collection
    {
        $firstGenre = mb_trim(explode(',', $genres)[0]);
        if ($firstGenre === '') {
            return new Collection();
        }

        return MovieDetail::query()
            ->where('genre', 'like', '%' . $firstGenre . '%')
            ->where('imdb_id', '!=', $excludedImdbId)
            ->orderByDesc('imdb_rating')
            ->take(6)
            ->get(self::CARD_COLUMNS);
    }
}
