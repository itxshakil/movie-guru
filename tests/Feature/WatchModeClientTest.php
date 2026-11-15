<?php

declare(strict_types=1);

use App\Services\WatchModeClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

test('requests are refused when no api key is configured instead of sending an empty key', function (): void {
    config()->set('services.watchmode.key', '');
    Http::fake();

    expect(fn () => resolve(WatchModeClient::class)->get('/sources/'))
        ->toThrow(Exception::class, 'No WatchMode API keys are configured.');

    Http::assertNothingSent();
});

test('requests use one of the configured keys, ignoring blanks and spaces', function (): void {
    config()->set('services.watchmode.key', ' key-one , ,key-two');
    Http::fake(['api.watchmode.com/*' => Http::response(['ok' => true])]);

    expect(resolve(WatchModeClient::class)->get('/sources/'))->toBe(['ok' => true]);

    Http::assertSent(fn (Request $request): bool => in_array($request['apiKey'], ['key-one', 'key-two'], strict: true));
});

test('a missing title is reported as an error result', function (): void {
    config()->set('services.watchmode.key', 'key-one');
    Http::fake(['api.watchmode.com/*' => Http::response(['success' => false, 'statusMessage' => 'Title not found'], 404)]);

    expect(resolve(WatchModeClient::class)->get('/title/tt0000001/details/'))->toBe(['error' => 'Title not found']);
});

test('a key over its plan quota rests for an hour instead of failing on every request', function (): void {
    config()->set('services.watchmode.key', 'key-one');
    Http::fake(['api.watchmode.com/*' => Http::response(
        ['success' => false, 'errorMessage' => 'Over plan quota on this API Key.'],
        429,
    )]);
    Log::spy();
    $client = resolve(WatchModeClient::class);

    expect($client->get('/title/tt0000001/sources/'))->toBe(['error' => 'WatchMode API key is over its quota'])
        ->and($client->get('/title/tt0000002/sources/'))->toBe(['error' => 'Every WatchMode API key is over its quota']);

    Http::assertSentCount(1);
    Log::shouldHaveReceived('warning')
        ->with('A WatchMode API key was turned away; resting it before trying again.', [
            'key_index' => 0,
            'reason' => 'plan quota',
            'pause_seconds' => WatchModeClient::QUOTA_PAUSE_SECONDS,
        ])
        ->once();
    Log::shouldNotHaveReceived('error');

    $this->travel(WatchModeClient::QUOTA_PAUSE_SECONDS + 1)->seconds();
    $client->get('/title/tt0000003/sources/');

    Http::assertSentCount(2);
});

test('requests move on to a key that still has quota', function (): void {
    Http::fake(['api.watchmode.com/*' => static fn (Request $request) => $request['apiKey'] === 'spent-key'
        ? Http::response(['success' => false, 'errorMessage' => 'Over plan quota on this API Key.'], 429)
        : Http::response(['ok' => true])]);
    config()->set('services.watchmode.key', 'spent-key');
    resolve(WatchModeClient::class)->get('/sources/');
    config()->set('services.watchmode.key', 'spent-key,fresh-key');
    $client = resolve(WatchModeClient::class);

    $results = collect(range(1, 10))->map(static fn (): array => $client->get('/sources/'));

    expect($results->unique()->all())->toBe([['ok' => true]])
        ->and(collect(Http::recorded())->skip(1)->pluck('0.apiKey')->unique()->all())->toBe(['fresh-key']);
});

test('a short rate limit rests the key for a minute only', function (): void {
    config()->set('services.watchmode.key', 'key-one');
    Http::fake(['api.watchmode.com/*' => Http::sequence()
        ->push(['success' => false, 'errorMessage' => 'Too many requests'], 429)
        ->push(['ok' => true])]);
    $client = resolve(WatchModeClient::class);

    $client->get('/sources/');
    $this->travel(WatchModeClient::RATE_LIMIT_PAUSE_SECONDS + 1)->seconds();

    expect($client->get('/sources/'))->toBe(['ok' => true]);
});
