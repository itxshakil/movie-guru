<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Http;

test('detail endpoint returns json for an xhr request that accepts json', function (): void {
    Http::fake();

    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt1234567',
        'sources' => ['Netflix'],
    ]);

    $this->get('/i/' . $movie->imdb_id, [
        'X-Requested-With' => 'XMLHttpRequest',
        'Accept' => 'application/json',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['detail', 'sources', 'affiliateLink', 'trailerId']);
});

test('detail endpoint renders a full inertia page for a plain link visit with no special headers', function (): void {
    Http::fake();

    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt7654321',
        'sources' => ['Netflix'],
    ]);

    $response = $this->get('/i/' . $movie->imdb_id);

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('text/html');
});
