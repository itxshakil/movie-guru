<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Exceptions;
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

test('a detail page view is recorded for analytics', function (): void {
    Http::fake();
    $movie = MovieDetail::factory()->create(['imdb_id' => 'tt7000001', 'sources' => ['Netflix']]);

    $this->get('/movie/' . $movie->imdb_id, ['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'])
        ->assertSuccessful();

    $this->assertDatabaseHas('show_page_analytics', ['imdb_id' => 'tt7000001']);
});

test('the service worker caching a saved title for offline use is not recorded as a view', function (): void {
    Http::fake();
    $movie = MovieDetail::factory()->create(['imdb_id' => 'tt7000002', 'sources' => ['Netflix']]);

    $this->get('/movie/' . $movie->imdb_id, [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36',
        'Purpose' => 'prefetch',
    ])->assertSuccessful();

    $this->assertDatabaseMissing('show_page_analytics', ['imdb_id' => 'tt7000002']);
    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'watchmode'));
});

test('a title first fetched from omdb is saved with the fields hidden gems and similar movies need', function (): void {
    Http::fake([
        'omdbapi.com/*' => Http::response([
            'Response' => 'True',
            'Title' => 'Paterson',
            'Year' => '2016',
            'Released' => '28 Dec 2016',
            'Poster' => 'https://example.com/paterson.jpg',
            'Type' => 'movie',
            'imdbRating' => '7.3',
            'imdbVotes' => '80,123',
            'Genre' => 'Comedy, Drama',
            'Director' => 'Jim Jarmusch',
            'Writer' => 'Jim Jarmusch',
            'Actors' => 'Adam Driver, Golshifteh Farahani',
        ]),
        '*' => Http::response([]),
    ]);

    $this->getJson('/i/tt5247022')
        ->assertSuccessful()
        ->assertJsonPath('detail.Title', 'Paterson');

    $this->assertDatabaseHas('movie_details', [
        'imdb_id' => 'tt5247022',
        'genre' => 'Comedy, Drama',
        'director' => 'Jim Jarmusch',
        'actors' => 'Adam Driver, Golshifteh Farahani',
        'imdb_votes' => '80123',
    ]);
});

test('an omdb miss is reported instead of crashing the background save', function (): void {
    Http::fake([
        'omdbapi.com/*' => Http::response(['Response' => 'False', 'Error' => 'Incorrect IMDb ID.']),
        '*' => Http::response([]),
    ]);
    Exceptions::fake();

    $this->getJson('/i/tt0000009')->assertSuccessful();

    $this->assertDatabaseMissing('movie_details', ['imdb_id' => 'tt0000009']);
    Exceptions::assertReported(fn (Exception $exception): bool => str_contains($exception->getMessage(), 'tt0000009'));
});
