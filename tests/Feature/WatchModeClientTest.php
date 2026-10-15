<?php

declare(strict_types=1);

use App\Services\WatchModeClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

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
