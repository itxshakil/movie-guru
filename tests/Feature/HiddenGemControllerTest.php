<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Cache;

beforeEach(fn () => Cache::flush());

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
