<?php

declare(strict_types=1);

use App\Exceptions\OmdbUnavailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

function fakeOmdbCloudflareChallenge(): void
{
    Http::fake([
        'omdbapi.com/*' => Http::response(
            '<!DOCTYPE html><html lang="en-US"><head><title>Just a moment...</title></head></html>',
            403,
            ['cf-mitigated' => 'challenge'],
        ),
    ]);
}

test('search responds with 503 instead of crashing when omdb serves a cloudflare challenge', function (): void {
    fakeOmdbCloudflareChallenge();

    $this->get(route('search', ['s' => 'Bala', 'page' => 2]))
        ->assertServiceUnavailable();
});

test('search does not cache the failed omdb response', function (): void {
    fakeOmdbCloudflareChallenge();

    $this->get(route('search', ['s' => 'Bala', 'page' => 2]));

    expect(Cache::has('searcgh-Bala-2--'))->toBeFalse();
});

test('a cloudflare challenge is not retried', function (): void {
    fakeOmdbCloudflareChallenge();

    $this->get(route('search', ['s' => 'Bala', 'page' => 2]));

    Http::assertSentCount(1);
});

test('an omdb server error is retried before giving up', function (): void {
    Sleep::fake();
    Http::fake(['omdbapi.com/*' => Http::response('error code: 1200', 503)]);

    $this->get(route('search', ['s' => 'Bala']))->assertServiceUnavailable();

    Http::assertSentCount(3);
});

test('an omdb outage reports once per hour instead of on every request', function (): void {
    Exceptions::fake();
    fakeOmdbCloudflareChallenge();

    $this->get(route('search', ['s' => 'Bala']));
    $this->get(route('search', ['s' => 'Batman']));
    $this->get(route('search', ['s' => 'Inception']));

    Exceptions::assertReportedCount(1);
    Exceptions::assertReported(OmdbUnavailableException::class);
});

test('a json search request gets a 503 json error during an omdb outage', function (): void {
    fakeOmdbCloudflareChallenge();

    $this->getJson(route('search', ['s' => 'Bala', 'page' => 2]))
        ->assertServiceUnavailable();
});

test('the detail page responds with 503 when omdb is unavailable for an uncached title', function (): void {
    fakeOmdbCloudflareChallenge();

    $this->get(route('movie.show', ['imdbID' => 'tt0372784']))
        ->assertServiceUnavailable();
});

test('stale cached search results keep being served when the background refresh hits an omdb outage', function (): void {
    fakeOmdbCloudflareChallenge();
    $staleResults = [
        'Response' => 'True',
        'totalResults' => '1',
        'Search' => [[
            'title' => 'Bala',
            'year' => '2019',
            'type' => 'movie',
            'imdb_id' => 'tt8869978',
            'poster' => 'N/A',
        ]],
    ];
    Cache::put('searcgh-Bala-1--', $staleResults, now()->addDay());
    Cache::put('illuminate:cache:flexible:created:searcgh-Bala-1--', now()->subDay()->getTimestamp(), now()->addDay());

    $this
        ->getJson(route('search', ['s' => 'Bala']))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Search.0.imdb_id', 'tt8869978');

    Http::assertSentCount(1);
    expect(Cache::get('searcgh-Bala-1--'))->toBe($staleResults);
});

test('an omdb connection failure is retried and then responds with 503', function (): void {
    Sleep::fake();
    Http::fake(['omdbapi.com/*' => Http::failedConnection('cURL error 28: Operation timed out')]);

    $this->get(route('search', ['s' => 'Bala']))->assertServiceUnavailable();

    Http::assertSentCount(3);
});

test('the omdb api key never reaches the omdb log', function (): void {
    config(['omdb.api_keys' => 'secret-test-key']);
    Log::spy();
    Log::shouldReceive('channel')->andReturnSelf();
    Sleep::fake();
    Http::fake([
        'omdbapi.com/*' => Http::failedConnection(
            'cURL error 28: Operation timed out for https://www.omdbapi.com/?apikey=secret-test-key&s=Bala',
        ),
    ]);

    $this->get(route('search', ['s' => 'Bala']))->assertServiceUnavailable();

    Log::shouldHaveReceived('warning')
        ->withArgs(
            fn (string $message, array $context): bool => ! str_contains(
                $message . json_encode($context),
                'secret-test-key',
            ),
        )
        ->once();
});
