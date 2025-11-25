<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Search;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class TrendingQueryService
{
    /**
     * @return Collection<int, string>
     */
    public function fetch(): Collection
    {
        return Cache::remember('trending-search-queries', now()->endOfDay(), function () {
            $queries = Search::recentOnly()->hasResults()->popular()->pluck('query');

            $titleCleaner = app(TitleCleaner::class);

            return $queries->map(fn(?string $query) => Str::title($titleCleaner->clean($query)))->unique()->values();
        });
    }
}
