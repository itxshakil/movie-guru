<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use App\Models\ShowPageAnalytics;
use App\Services\MovieCollectionService;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

test('every home page movie section carries the genre the cards show as chips', function (string $section): void {
    $newRelease = MovieDetail::factory()->create([
        'year' => now()->format('Y'),
        'imdb_rating' => '8.9',
        'imdb_votes' => '50000',
        'genre' => 'Sci-Fi, Thriller',
    ]);
    MovieDetail::factory()->create([
        'year' => '1999',
        'imdb_rating' => '9.0',
        'imdb_votes' => '200000',
        'genre' => 'Sci-Fi, Thriller',
    ]);
    ShowPageAnalytics::factory()->create(['imdb_id' => $newRelease->imdb_id]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where($section . '.0.genre', 'Sci-Fi, Thriller'));
})->with([
    'popularMovies',
    'trendingMovies',
    'hiddenGemsMovies',
    'recentlyReleasedMovies',
    'topRatedMovies',
    'recommendedMovies',
    'mostWatchedMovies',
]);

test('similar titles carry the genre the cards show as chips', function (): void {
    MovieDetail::factory()->create(['genre' => 'Drama, Crime']);

    $similar = resolve(MovieCollectionService::class)->similarTo('Drama', 'tt0000000');

    expect($similar)->toHaveCount(1)
        ->and($similar->first()->genre)->toBe('Drama, Crime');
});

test('card rows cached before the card payload changed are not served', function (string $legacyKey, string $section): void {
    $newRelease = MovieDetail::factory()->create([
        'year' => now()->format('Y'),
        'imdb_rating' => '8.9',
        'imdb_votes' => '50000',
        'genre' => 'Sci-Fi, Thriller',
    ]);
    MovieDetail::factory()->create([
        'year' => '1999',
        'imdb_rating' => '9.0',
        'imdb_votes' => '200000',
        'genre' => 'Sci-Fi, Thriller',
    ]);
    ShowPageAnalytics::factory()->create(['imdb_id' => $newRelease->imdb_id]);
    Cache::put($legacyKey, MovieDetail::query()->get(['imdb_id', 'title']), now()->addDay());

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->where($section . '.0.genre', 'Sci-Fi, Thriller'));
})->with([
    ['popular-movies', 'popularMovies'],
    ['hidden-gems-movies', 'hiddenGemsMovies'],
    ['top-rated-movies', 'topRatedMovies'],
    ['trending-movies', 'trendingMovies'],
    ['recently-released-movies', 'recentlyReleasedMovies'],
    ['recommended-movies', 'recommendedMovies'],
    ['most-watched-movies', 'mostWatchedMovies'],
]);
