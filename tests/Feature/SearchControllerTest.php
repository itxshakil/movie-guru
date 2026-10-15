<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

function fakeOmdbSearchResponse(): void
{
    Http::fake([
        'omdbapi.com/*' => Http::response([
            'Response' => 'True',
            'totalResults' => '25',
            'Search' => [
                ['Title' => 'Batman Begins', 'Year' => '2005', 'Type' => 'movie', 'imdbID' => 'tt0372784', 'Poster' => 'N/A'],
            ],
        ]),
    ]);
}

test('search endpoint returns json for an xhr request that accepts json', function (): void {
    fakeOmdbSearchResponse();

    $this->get(route('search', ['s' => 'batman', 'page' => 1]), [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['searchResults', 'search', 'page', 'nextUrl']);
});

test('search endpoint renders a full inertia page for a plain link visit with no special headers', function (): void {
    fakeOmdbSearchResponse();

    $response = $this->get(route('search', ['s' => 'batman', 'page' => 2]));

    $response->assertSuccessful();

    expect($response->headers->get('content-type'))->toContain('text/html');
});
