<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
});

test('hidden gem endpoint returns a qualifying movie with imdb_id for the detail modal', function (): void {
    MovieDetail::factory()->create([
        'imdb_rating' => '8.0',
        'imdb_votes' => '10000',
        'poster' => 'https://example.com/poster.jpg',
    ]);

    $this->getJson(route('hidden-gem.show'))
        ->assertSuccessful()
        ->assertJsonStructure(['imdb_id', 'title', 'year', 'poster', 'imdb_rating', 'genre', 'director']);
});

test('hidden gem endpoint excludes movies with too many votes', function (): void {
    MovieDetail::factory()->create([
        'imdb_rating' => '8.0',
        'imdb_votes' => '500000',
        'poster' => 'https://example.com/poster.jpg',
    ]);

    $response = $this->getJson(route('hidden-gem.show'))->assertSuccessful();

    expect($response->json())->toBeEmpty();
});

test('shuffling returns a different qualifying gem than the excluded one', function (): void {
    MovieDetail::factory()->create([
        'imdb_id' => 'tt8000001',
        'imdb_rating' => '8.0',
        'imdb_votes' => '10000',
        'poster' => 'https://example.com/one.jpg',
    ]);
    MovieDetail::factory()->create([
        'imdb_id' => 'tt8000002',
        'imdb_rating' => '7.5',
        'imdb_votes' => '20000',
        'poster' => 'https://example.com/two.jpg',
    ]);

    $this->getJson(route('hidden-gem.show', ['shuffle' => 1, 'exclude' => 'tt8000001']))
        ->assertSuccessful()
        ->assertJsonPath('imdb_id', 'tt8000002');
});

test('shuffling never returns a movie that is not a hidden gem', function (): void {
    MovieDetail::factory()->create([
        'imdb_rating' => '9.0',
        'imdb_votes' => '900000',
        'poster' => 'https://example.com/blockbuster.jpg',
    ]);

    $response = $this->getJson(route('hidden-gem.show', ['shuffle' => 1]))->assertSuccessful();

    expect($response->json())->toBeEmpty();
});

test('shuffle input is validated', function (): void {
    $this->getJson(route('hidden-gem.show', ['shuffle' => 'yes please']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('shuffle');
});
