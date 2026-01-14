<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Search;
use App\Services\TrendingQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Override;
use Tests\TestCase;

final class TrendingQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fetches_trending_queries(): void
    {
        Search::create(['query' => 'inception', 'total_results' => 10, 'search_count' => 5]);
        Search::create(['query' => 'avatar full movie', 'total_results' => 20, 'search_count' => 10]);
        Search::create(['query' => 'no results', 'total_results' => 0, 'search_count' => 1]);

        $oldSearch = new Search(['query' => 'old query', 'total_results' => 30, 'search_count' => 15]);
        $oldSearch->updated_at = now()->subDays(30);
        $oldSearch->save();

        $trendingQueryService = new TrendingQueryService();
        $results = $trendingQueryService->fetch();

        $this->assertCount(2, $results);
        $this->assertEquals('Avatar', $results[0]); // cleaned and Titled, ordered by total_results desc
        $this->assertEquals('Inception', $results[1]);
    }

    public function test_it_caches_trending_queries(): void
    {
        Search::create(['query' => 'inception', 'total_results' => 10, 'search_count' => 5]);

        $trendingQueryService = new TrendingQueryService();

        $results1 = $trendingQueryService->fetch();

        // Change database record
        Search::query()->update(['query' => 'changed']);

        $results2 = $trendingQueryService->fetch();

        $this->assertEquals($results1, $results2);
        $this->assertEquals('Inception', $results2[0]);
    }

    public function test_it_returns_unique_cleaned_queries(): void
    {
        Search::create(['query' => 'inception', 'total_results' => 20, 'search_count' => 5]);
        Search::create(['query' => 'inception full movie', 'total_results' => 10, 'search_count' => 10]);

        $trendingQueryService = new TrendingQueryService();
        $results = $trendingQueryService->fetch();

        $this->assertCount(1, $results);
        $this->assertEquals('Inception', $results[0]);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }
}
