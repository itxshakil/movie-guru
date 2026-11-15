<?php

declare(strict_types=1);

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

test('search page loads without a query before any trending searches exist', function (): void {
    fakeOmdbSearchResponse();

    $this->get(route('search'))->assertSuccessful();
});
