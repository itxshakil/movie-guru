<?php

declare(strict_types=1);

use App\Services\WatchModeService;

it('extracts the youtube id from common url formats', function (string $url, string $expected): void {
    expect(WatchModeService::extractYoutubeId($url))->toBe($expected);
})->with([
    'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'short url' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'embed url' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'watch url with extra params' => [
        'https://www.youtube.com/watch?list=PL123&v=dQw4w9WgXcQ&t=10s',
        'dQw4w9WgXcQ',
    ],
]);

it('returns null for missing or invalid urls', function (?string $url): void {
    expect(WatchModeService::extractYoutubeId($url))->toBeNull();
})->with([
    'null' => [null],
    'empty' => [''],
    'not youtube' => ['https://example.com/video/abc'],
    'no id' => ['https://www.youtube.com/watch'],
]);
