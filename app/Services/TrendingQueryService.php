<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SearchQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TrendingQueryService
{
    /**
     * @return Collection<int, string>
     */
    public function fetch(): Collection
    {
        return Cache::remember('trending-search-queries', now()->endOfDay(), function () {
            $queries = SearchQuery::where('created_at', '>', now()->subDays(7)->startOfDay())->distinct()->pluck(
                'query'
            );

            $titleCleaner = app(TitleCleaner::class);

            return $queries->map(function ($query) use ($titleCleaner) {
                return Str::title($titleCleaner->clean($query));
            })->unique()->values();
        });
    }
}
