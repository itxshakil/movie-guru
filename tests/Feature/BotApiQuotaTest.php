<?php

declare(strict_types=1);

use App\Models\MovieDetail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function fakeOmdbTitle(string $imdbId, string $title): void
{
    Http::fake([
        'omdbapi.com/*' => Http::response([
            'Response' => 'True',
            'Title' => $title,
            'Year' => '2016',
            'Released' => '28 Dec 2016',
            'Type' => 'movie',
            'imdbID' => $imdbId,
            'Poster' => 'N/A',
        ]),
        '*' => Http::response([]),
    ]);
}

test('a crawler on a title we already store is served from the database without calling omdb', function (): void {
    Http::fake();
    MovieDetail::factory()->create(['imdb_id' => 'tt8000001', 'sources' => ['Netflix']]);

    $this->get('/movie/tt8000001', requestHeadersFor(CRAWLER_USER_AGENT))->assertSuccessful();

    Http::assertNothingSent();
});

test('a crawler on a cached title keeps the cached copy and never revalidates it against omdb', function (): void {
    Http::fake();
    Cache::flexible('detail.tt8000002', [now()->addHours(18), now()->addHours(24)], static fn (): array => [
        'Response' => 'True',
        'Title' => 'Cached Title',
    ]);
    $this->travel(20)->hours();

    $this->getJson('/movie/tt8000002', requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('detail.Title', 'Cached Title');

    Http::assertNothingSent();
});

test('a crawler on an unknown title spends the daily bot budget on one omdb call', function (): void {
    fakeOmdbTitle('tt8000003', 'Budgeted Title');

    $this->getJson('/movie/tt8000003', requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('detail.Title', 'Budgeted Title');

    Http::assertSentCount(1);
});

test('a crawler on an unknown title gets a 503 to retry later once the daily bot budget is spent', function (): void {
    Http::fake();
    config(['omdb.bot_daily_budget' => 0]);

    $response = $this->get('/movie/tt8000004', requestHeadersFor(CRAWLER_USER_AGENT))->assertServiceUnavailable();

    expect((int) $response->headers->get('Retry-After'))->toBeGreaterThan(0);
    Http::assertNothingSent();
});

test('the retry-after header survives the error page outside local and testing', function (): void {
    Http::fake();
    config(['omdb.bot_daily_budget' => 0]);
    $this->app->detectEnvironment(static fn (): string => 'production');

    $response = $this->get('/movie/tt8000005', requestHeadersFor(CRAWLER_USER_AGENT))->assertServiceUnavailable();

    expect((int) $response->headers->get('Retry-After'))->toBeGreaterThan(0);
});

test('people still get unknown titles from omdb after the bot budget is spent', function (): void {
    fakeOmdbTitle('tt8000006', 'Human Title');
    config(['omdb.bot_daily_budget' => 0]);

    $this->getJson('/movie/tt8000006', requestHeadersFor(BROWSER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('detail.Title', 'Human Title');
});

test('a stored title is refreshed from omdb at most once a week however often its cache expires', function (): void {
    fakeOmdbTitle('tt8000007', 'Refreshed Title');
    MovieDetail::factory()->create(['imdb_id' => 'tt8000007', 'sources' => ['Netflix']]);

    $this->getJson('/movie/tt8000007', requestHeadersFor(BROWSER_USER_AGENT))->assertSuccessful();
    Cache::forget('detail.tt8000007');
    $this->getJson('/movie/tt8000007', requestHeadersFor(BROWSER_USER_AGENT))->assertSuccessful();

    Http::assertSentCount(1);
});

test('a crawler search with nothing cached spends the daily bot budget on one omdb call', function (): void {
    fakeOmdbSearchResponse('1');

    $this->getJson(route('search', ['s' => 'batman']), requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Search.0.title', 'Batman Begins');

    Http::assertSentCount(1);
});

test('a crawler search reuses the cached omdb results without calling omdb', function (): void {
    fakeOmdbSearchResponse('1');
    $this->getJson(route('search', ['s' => 'batman']), requestHeadersFor(BROWSER_USER_AGENT))->assertSuccessful();
    config(['omdb.bot_daily_budget' => 0]);
    $this->travel(20)->hours();

    $this->getJson(route('search', ['s' => 'batman']), requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Search.0.title', 'Batman Begins');

    Http::assertSentCount(1);
});

test('a crawler search falls back to the titles we store once the daily bot budget is spent', function (): void {
    Http::fake();
    config(['omdb.bot_daily_budget' => 0]);
    MovieDetail::factory()->create(['imdb_id' => 'tt8000008', 'title' => 'Paterson', 'year' => '2016', 'poster' => 'N/A']);
    MovieDetail::factory()->create(['imdb_id' => 'tt8000009', 'title' => 'Inception', 'year' => '2010']);

    $this->getJson(route('search', ['s' => 'paterson']), requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Response', 'True')
        ->assertJsonPath('searchResults.totalResults', '1')
        ->assertJsonCount(1, 'searchResults.Search')
        ->assertJsonPath('searchResults.Search.0.imdb_id', 'tt8000008')
        ->assertJsonPath('searchResults.Search.0.poster', 'N/A');

    Http::assertNothingSent();
});

test('a crawler search fallback is kept apart from the cache people use, so they still get the full omdb results', function (): void {
    fakeOmdbSearchResponse('1');
    config(['omdb.bot_daily_budget' => 0]);

    $this->getJson(route('search', ['s' => 'batman']), requestHeadersFor(CRAWLER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Response', 'False');

    $this->getJson(route('search', ['s' => 'batman']), requestHeadersFor(BROWSER_USER_AGENT))
        ->assertSuccessful()
        ->assertJsonPath('searchResults.Search.0.title', 'Batman Begins');
});

test('one address cannot get around the search throttle by changing the query each time', function (): void {
    fakeOmdbSearchResponse('1');

    foreach (range(1, 30) as $attempt) {
        $this->getJson(route('search', ['s' => 'query ' . $attempt]), requestHeadersFor(BROWSER_USER_AGENT))->assertSuccessful();
    }

    $this->getJson(route('search', ['s' => 'one more']), requestHeadersFor(BROWSER_USER_AGENT))->assertTooManyRequests();
});

test('bots share one daily budget, so new titles past it get a 503 while people are unaffected', function (): void {
    fakeOmdbTitle('tt8000010', 'Any Title');
    config(['omdb.bot_daily_budget' => 2]);

    $this->getJson('/movie/tt8000010', requestHeadersFor(CRAWLER_USER_AGENT))->assertSuccessful();
    $this->getJson('/movie/tt8000011', requestHeadersFor(CRAWLER_USER_AGENT))->assertSuccessful();
    $this->getJson('/movie/tt8000012', requestHeadersFor(CRAWLER_USER_AGENT))->assertServiceUnavailable();
    $this->getJson('/movie/tt8000012', requestHeadersFor(BROWSER_USER_AGENT))->assertSuccessful();

    Http::assertSentCount(3);
});

test('scripted http clients and link preview fetchers are treated as bots', function (string $userAgent): void {
    Http::fake();
    config(['omdb.bot_daily_budget' => 0]);

    $this->get('/movie/tt8000013', requestHeadersFor($userAgent))->assertServiceUnavailable();

    Http::assertNothingSent();
})->with([
    'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
    'curl/8.4.0',
    'python-requests/2.31.0',
    'Go-http-client/2.0',
    'okhttp/4.12.0',
    'axios/1.6.0',
    'Scrapy/2.11.0 (+https://scrapy.org)',
    'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)',
]);
