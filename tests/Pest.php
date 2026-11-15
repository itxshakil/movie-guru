<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
 |--------------------------------------------------------------------------
 | Test Case
 |--------------------------------------------------------------------------
 |
 | The closure you provide to your test functions is always bound to a specific PHPUnit test
 | case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
 | need to change it using the "uses()" function to bind a different classes or traits.
 |
 */

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/*
 |--------------------------------------------------------------------------
 | Expectations
 |--------------------------------------------------------------------------
 |
 | When you're writing tests, you often need to check that values meet certain conditions. The
 | "expect()" function gives you access to a set of "expectations" methods that you can use
 | to assert different things. Of course, you may extend the Expectation API at any time.
 |
 */

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
 |--------------------------------------------------------------------------
 | Functions
 |--------------------------------------------------------------------------
 |
 | While Pest is very powerful out-of-the-box, you may have some testing code specific to your
 | project that you don't want to repeat in every file. Here you can also expose helpers as
 | global functions to help you to reduce the number of lines of code in your test files.
 |
 */

const BROWSER_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

const CRAWLER_USER_AGENT = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

/**
 * Headers a real browser or crawler sends, so the bot check decides by user agent alone.
 *
 * @return array<string, string>
 */
function requestHeadersFor(string $userAgent): array
{
    return [
        'User-Agent' => $userAgent,
        'Accept-Language' => 'en-US,en;q=0.9',
        'Accept-Encoding' => 'gzip, deflate, br',
    ];
}

function fakeOmdbSearchResponse(string $totalResults = '25'): void
{
    Http::fake([
        'omdbapi.com/*' => Http::response([
            'Response' => 'True',
            'totalResults' => $totalResults,
            'Search' => [
                ['Title' => 'Batman Begins', 'Year' => '2005', 'Type' => 'movie', 'imdbID' => 'tt0372784', 'Poster' => 'N/A'],
            ],
        ]),
    ]);
}
