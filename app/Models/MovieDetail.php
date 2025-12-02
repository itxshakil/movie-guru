<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property-read Carbon $source_last_fetched_at
 */
final class MovieDetail extends Model
{
    protected $fillable = [
        'imdb_id',
        'title',
        'year',
        'release_date',
        'poster',
        'type',
        'imdb_rating',
        'imdb_votes',
        'genre',
        'director',
        'writer',
        'actors',
        'details',
        'sources',
        'source_last_fetched_at',
        'views',
    ];

    public function incrementViews(string $ipAddress): void
    {
        $cacheKey = 'movie-view-' . $this->imdb_id . '-' . $ipAddress;

        if (!Cache::has($cacheKey)) {
            $this->increment('views');
            Cache::put($cacheKey, true, now()->addHour());
        }
    }

    #[Scope]
    protected function topRated(Builder $query): void
    {
        $query->where(function (\Illuminate\Contracts\Database\Query\Builder $query): void {
            $query->where(function (Builder $query): void {
                $query->where('imdb_rating', '>=', 8.5)
                    ->where('imdb_votes', '>', 80_000);
            })->orWhere(function (Builder $query): void {
                $query->whereBetween('imdb_rating', [8.0, 8.5])
                    ->where('imdb_votes', '>', 1_00_000);
            });
        });
    }

    #[Scope]
    protected function trending(Builder $query): void
    {
        $query->recentlyReleased()
            ->where(function (Builder $query): void {
                $query->where(function (\Illuminate\Contracts\Database\Query\Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query->where('imdb_rating', '>=', 8.5)
                            ->where('imdb_votes', '>', 8_000);
                    })->orWhere(function (Builder $query): void {
                        $query->whereBetween('imdb_rating', [8.0, 8.5])
                            ->where('imdb_votes', '>', 10_000);
                    });
                });
            });
    }

    #[Scope]
    protected function recentlyReleased(Builder $query): void
    {
        $query->where('year', now()->format('Y'));
    }

    #[Scope]
    protected function recommended(Builder $query): void
    {
        $query->where(function (Builder $query): void {
            $query->where(function (Builder $q): void {
                $q->where('imdb_rating', '>', 7.0)
                    ->where('imdb_rating', '<=', 7.5)
                    ->where('imdb_votes', '>', 50_000);
            })->orWhere(function (Builder $q): void {
                $q->where('imdb_rating', '>', 7.5)
                    ->where('imdb_votes', '>', 30_000);
            });
        });
    }

    #[Scope]
    protected function hiddenGems(Builder $query): void
    {
        $query->where(function (\Illuminate\Contracts\Database\Query\Builder $query): void {
            $query->where('imdb_rating', '>', 8.5)
                ->whereBetween('imdb_votes', [3_000, 80_000]);
        });
    }

    #[Scope]
    protected function popular(Builder $query): void
    {
        $query->orderBy('views', 'desc');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'details' => 'json',
            'sources' => 'json',
            'source_last_fetched_at' => 'datetime',
        ];
    }
}
