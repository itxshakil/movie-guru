<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MovieDetailFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MovieDetail extends Model
{
    /** @use HasFactory<MovieDetailFactory> */
    use HasFactory;

    protected $fillable = [
        'imdb_id',
        'title',
        'year',
        'release_date',
        'poster',
        'type',
        'imdb_rating',
        'imdb_votes',
        'details',
        'views',
    ];

    public function incrementViews(): void
    {
        $ipAddress = request()->ip();
        $cacheKey = 'movie-view-'.$this->imdb_id.'-'.$ipAddress;

        if (!Cache::has($cacheKey)) {
            $this->increment('views');
            Cache::put($cacheKey, true, now()->addHour());
        }
    }

    public function scopeTopRated(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where(function (Builder $query) {
                $query->where('imdb_rating', '>=', 8.5)
                    ->where('imdb_votes', '>', 80_000);
            })->orWhere(function (Builder $query) {
                $query->whereBetween('imdb_rating', [8.0, 8.5])
                    ->where('imdb_votes', '>', 1_00_000);
            });
        });
    }

    public function scopeTrending(Builder $query): void
    {
        $query->topRated()->recentlyReleased();
    }

    public function scopeRecentlyReleased(Builder $query): void
    {
        $query->where('year', now()->format('Y'));
    }

    public function scopeRecommended(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->where('imdb_rating', '>', 7.5)
                ->where('imdb_votes', '>', 30_000);
        });
    }

    public function scopeHiddenGems(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where('imdb_rating', '>', 8.5)
                ->whereBetween('imdb_votes', [3_000, 80_000]);
        });
    }

    public function scopePopular(Builder $query): void
    {
        $query->orderBy('views', 'desc');
    }

    protected function casts(): array
    {
        return [
            'details' => 'json',
        ];
    }
}
