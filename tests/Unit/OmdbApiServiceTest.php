<?php

namespace Tests\Unit;

use App\Services\OMDBApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OmdbApiServiceTest extends TestCase
{
    protected $omdbApiService;

    public function setUp(): void
    {
        parent::setUp();
        $this->omdbApiService = new OMDBApiService();
    }

    public function testGetByTitle()
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['Title' => 'Test Movie'], 200),
        ]);

        $response = $this->omdbApiService->getByTitle('Test Movie');

        $this->assertEquals(['Title' => 'Test Movie'], $response);
    }

    public function testGetById()
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['imdbID' => 'tt1234567'], 200),
        ]);

        $response = $this->omdbApiService->getById('tt1234567');

        $this->assertEquals(['imdbID' => 'tt1234567'], $response);
    }

    public function testSearchByTitle()
    {
        Http::fake([
            'www.omdbapi.com/*' => Http::response(['Search' => [['Title' => 'Test Movie']]], 200),
        ]);

        $response = $this->omdbApiService->searchByTitle('Test Movie');

        $this->assertEquals(['Search' => [['Title' => 'Test Movie']]], $response);
    }
}
