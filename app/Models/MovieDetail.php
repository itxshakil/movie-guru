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
            $query->where('imdb_rating', '>', 8)
                ->orWhere(function ($query) {
                    $query->where('imdb_rating', '=', 8)
                        ->where('imdb_votes', '>', 1_00_000);
                });
        });
    }

    public function scopeTrending(Builder $query): void
    {
        $query->topRated()->recentlyReleased();
    }

    public function scopeHiddenGems(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where('imdb_rating', '>', 8.5)
                ->where('imdb_votes', '<', 1_00_000)
                ->where('imdb_votes', '>', 30_000);
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
