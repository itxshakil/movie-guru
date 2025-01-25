<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Search extends Model
{
    protected $fillable = [
        'query',
        'total_results',
        'search_count',
    ];

    protected $casts = [
        'search_count' => 'integer',
        'total_results' => 'integer',
    ];

    public function incrementViews(): void
    {
        $ipAddress = request()->ip();
        $cacheKey = 'search-query-'.$this->query.'-'.$ipAddress;

        if (!Cache::has($cacheKey)) {
            $this->increment('search_count');
            Cache::put($cacheKey, true, now()->addHour());
        }
    }

    public function scopeHasResults($query): void
    {
        $query->where('total_results', '>', 0);
    }

    public function scopeRecentOnly(Builder $query, int $days = 28): void
    {
        $query->where('updated_at', '>', now()->subDays($days));
    }

    public function scopePopular(Builder $query): void
    {
        $query->orderByDesc('total_results');
    }
}
