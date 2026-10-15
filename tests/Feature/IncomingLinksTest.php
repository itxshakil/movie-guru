<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function (): void {
    Http::fake([
        'omdbapi.com/*' => Http::response([
            'Response' => 'True',
            'totalResults' => '1',
            'Search' => [
                ['Title' => 'Batman Begins', 'Year' => '2005', 'Type' => 'movie', 'imdbID' => 'tt0372784', 'Poster' => 'N/A'],
            ],
        ]),
    ]);
});

test('sharing an imdb link in the text field opens the title', function (): void {
    $this->get(route('search', ['s' => 'Check this out https://m.imdb.com/title/tt0111161/?ref_=ext_shr_lnk']))
        ->assertRedirect(route('movie.show', ['imdbID' => 'tt0111161']));
});

test('sharing an imdb link in the url field opens the title', function (): void {
    $this->get(route('search', ['name' => 'The Shawshank Redemption', 'link' => 'https://www.imdb.com/title/tt0111161/']))
        ->assertRedirect(route('movie.show', ['imdbID' => 'tt0111161']));
});

test('sharing a slug based link searches for its title', function (string $sharedUrl, string $expectedTitle): void {
    $this->get(route('search', ['link' => $sharedUrl]))
        ->assertRedirect(route('search', ['s' => $expectedTitle]));
})->with([
    'letterboxd' => ['https://letterboxd.com/film/the-dark-knight/', 'the dark knight'],
    'letterboxd with year suffix' => ['https://letterboxd.com/film/dune-2021/', 'dune'],
    'rotten tomatoes' => ['https://www.rottentomatoes.com/m/the_dark_knight', 'the dark knight'],
    'justwatch' => ['https://www.justwatch.com/in/movie/oppenheimer', 'oppenheimer'],
]);

test('sharing plain text still runs a normal search', function (): void {
    $this->get(route('search', ['s' => 'batman']))
        ->assertSuccessful();
});

test('sharing an unknown link still runs a normal search', function (): void {
    $this->get(route('search', ['s' => 'batman', 'link' => 'https://example.com/film/not-a-movie-site']))
        ->assertSuccessful();
});

test('json search requests are never redirected', function (): void {
    $this->getJson(route('search', ['s' => 'tt0111161']))
        ->assertSuccessful()
        ->assertJsonStructure(['searchResults']);
});

test('protocol links open the matching page', function (string $protocolUrl, string $expectedUrl): void {
    $this->get(route('protocol-link.show', ['url' => $protocolUrl]))
        ->assertRedirect($expectedUrl);
})->with([
    'title' => fn (): array => ['web+movieguru://i/tt0111161', route('movie.show', ['imdbID' => 'tt0111161'])],
    'title without slashes' => fn (): array => ['web+movieguru:tt0111161', route('movie.show', ['imdbID' => 'tt0111161'])],
    'search path' => fn (): array => ['web+movieguru://search/the%20matrix', route('search', ['s' => 'the matrix'])],
    'search query' => fn (): array => ['web+movieguru://search?s=the+matrix', route('search', ['s' => 'the matrix'])],
    'mood' => fn (): array => ['web+movieguru://mood/cozy', route('mood.show', ['mood' => 'cozy'])],
    'movie match' => function (): array {
        $matchToken = Str::random(32);

        return ['web+movieguru://movie-match/' . $matchToken, route('movie-match.show', ['token' => $matchToken])];
    },
    'watchlist' => fn (): array => ['web+movieguru://watchlist', route('watchlist')],
    'unknown path' => fn (): array => ['web+movieguru://something-else', route('home')],
]);

test('protocol links never redirect off site', function (string $protocolUrl): void {
    $this->get(route('protocol-link.show', ['url' => $protocolUrl]))
        ->assertRedirect(route('home'));
})->with([
    'other scheme' => ['https://evil.example.com'],
    'protocol relative' => ['web+movieguru:////evil.example.com'],
    'javascript' => ['javascript:alert(1)'],
]);

test('protocol links require a url', function (): void {
    $this->get(route('protocol-link.show'))
        ->assertSessionHasErrors('url');
});
