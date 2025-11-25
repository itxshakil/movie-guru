<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OMDBApiService;
use Illuminate\Support\Facades\Http;
use Override;
use Tests\TestCase;

final class OmdbApiServiceTest extends TestCase
{
    private OMDBApiService $omdbApiService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->omdbApiService = new OMDBApiService();
    }

    public function test_get_by_title(): void
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['Title' => 'Test Movie'], 200),
        ]);

        $response = $this->omdbApiService->getByTitle('Test Movie');

        $this->assertEquals(['Title' => 'Test Movie'], $response);
    }

    public function test_get_by_id(): void
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['imdbID' => 'tt1234567'], 200),
        ]);

        $response = $this->omdbApiService->getById('tt1234567');

        $this->assertEquals(['imdbID' => 'tt1234567'], $response);
    }

    public function test_search_by_title(): void
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['Search' => [['Title' => 'Test Movie']]], 200),
        ]);

        $response = $this->omdbApiService->searchByTitle('Test Movie');

        $this->assertEquals(['Search' => [['Title' => 'Test Movie']]], $response);
    }
}
