<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\SearchCriteria;
use App\Models\Search;
use App\Models\SearchQuery;
use Illuminate\Http\Request;

/**
 * Logs a search and its result, and counts it towards the query's popularity. Runs after the response is sent.
 */
final readonly class RecordSearch
{
    public function handle(Request $request, SearchCriteria $criteria, mixed $movies): void
    {
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        defer(static function () use ($criteria, $ipAddress, $userAgent, $movies): void {
            SearchQuery::query()->create([
                'query' => $criteria->search,
                'page' => $criteria->page,
                'type' => $criteria->movieType,
                'year' => $criteria->year,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'response_at' => now(),
                'response' => $movies['Response'] === 'True',
                'response_result' => $movies,
            ]);

            Search::query()->updateOrCreate([
                'query' => $criteria->search,
            ], [
                'total_results' => $movies['totalResults'] ?? 0,
            ])->incrementViews();
        });
    }
}
