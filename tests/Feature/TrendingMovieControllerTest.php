<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

test('trending endpoint lists todays trending movies for the badge refresh', function (): void {
    MovieDetail::factory()->create([
        'imdb_id' => 'tt9000001',
        'year' => now()->format('Y'),
        'imdb_rating' => '8.7',
        'imdb_votes' => '20000',
    ]);

    $this->getJson(route('trending.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.imdb_id', 'tt9000001')
        ->assertJsonStructure(['data' => [['imdb_id', 'title', 'year', 'poster']]]);
});

test('trending endpoint excludes movies that are not trending', function (): void {
    MovieDetail::factory()->create([
        'year' => '1999',
        'imdb_rating' => '9.0',
        'imdb_votes' => '200000',
    ]);

    $this->getJson(route('trending.index'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

test('home page still renders the same trending movies', function (): void {
    MovieDetail::factory()->create([
        'imdb_id' => 'tt9000002',
        'year' => now()->format('Y'),
        'imdb_rating' => '8.9',
        'imdb_votes' => '50000',
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('trendingMovies.0.imdb_id', 'tt9000002'));
});
