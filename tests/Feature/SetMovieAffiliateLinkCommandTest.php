<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sets affiliate link using direct arguments', function (): void {
    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt1234567',
        'title' => 'Inception',
    ]);

    $this->artisan('movie:affiliate', [
        'imdbid' => 'tt1234567',
        'title' => 'PVR Cinemas',
        'link' => 'https://pvr.com/inception',
    ])->assertExitCode(0);

    $movie->refresh();

    expect($movie->affiliate_link)->toBe([
        'link' => 'https://pvr.com/inception',
        'title' => 'PVR Cinemas',
    ]);
});

it('prompts for affiliate link with google search default when link argument is omitted', function (): void {
    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt1234567',
        'title' => 'Inception',
    ]);

    $expectedDefault = 'https://www.google.com/search?q=' . urlencode('PVR Cinemas movie tickets Inception');

    $this->artisan('movie:affiliate', [
        'imdbid' => 'tt1234567',
        'title' => 'PVR Cinemas',
    ])
        ->expectsQuestion('Enter the affiliate link', $expectedDefault)
        ->assertExitCode(0);

    $movie->refresh();

    expect($movie->affiliate_link['title'])->toBe('PVR Cinemas')
        ->and($movie->affiliate_link['link'])->toBe($expectedDefault);
});

it('returns error when movie is not found by imdb id', function (): void {
    $this->artisan('movie:affiliate', [
        'imdbid' => 'tt9999999',
        'title' => 'Unknown',
    ])
        ->expectsOutput('Movie with IMDB ID tt9999999 not found.')
        ->assertExitCode(1);
});

it('uses search prompt when imdbid is not provided', function (): void {
    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt7654321',
        'title' => 'The Matrix',
        'year' => '1999',
        'type' => 'movie',
    ]);

    $this->artisan('movie:affiliate', ['title' => 'Book Now', 'link' => 'https://booknow.com'])
        ->expectsSearch(
            'Search for a movie by title',
            search: 'Matrix',
            answers: [
                'tt7654321' => 'The Matrix (1999) [movie]',
            ],
            answer: 'tt7654321',
        )
        ->assertExitCode(0);

    $movie->refresh();

    expect($movie->affiliate_link)->toBe([
        'link' => 'https://booknow.com',
        'title' => 'Book Now',
    ]);
});

it('uses text prompt for title when title is not provided', function (): void {
    $movie = MovieDetail::factory()->create([
        'imdb_id' => 'tt1234567',
        'title' => 'Inception',
    ]);

    $this->artisan('movie:affiliate', ['imdbid' => 'tt1234567', 'link' => 'https://pvr.com'])
        ->expectsQuestion('Enter the affiliate title label', 'PVR Cinemas')
        ->assertExitCode(0);

    $movie->refresh();

    expect($movie->affiliate_link)->toBe([
        'link' => 'https://pvr.com',
        'title' => 'PVR Cinemas',
    ]);
});
