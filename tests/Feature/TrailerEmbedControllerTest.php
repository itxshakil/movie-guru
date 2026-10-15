<?php

declare(strict_types=1);

test('the trailer embed page hosts the youtube player for picture-in-picture', function (): void {
    $this->get(route('trailer-embed.show', ['videoId' => 'dQw4w9WgXcQ', 'start' => 42]))
        ->assertSuccessful()
        ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'")
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1&amp;rel=0&amp;modestbranding=1&amp;start=42', false);
});

test('the trailer embed page starts from the beginning without a start time', function (): void {
    $this->get(route('trailer-embed.show', ['videoId' => 'dQw4w9WgXcQ']))
        ->assertSuccessful()
        ->assertDontSee('start=', false);
});

test('the trailer embed page only accepts youtube video ids', function (string $videoId): void {
    $this->get('/trailer-embed/' . $videoId)->assertNotFound();
})->with([
    'too short' => ['abc'],
    'script injection' => ['%22%3E%3Cscript%3E'],
    'too long' => ['dQw4w9WgXcQdQw4w9WgXcQ'],
]);

test('the trailer embed start time is validated', function (): void {
    $this->get(route('trailer-embed.show', ['videoId' => 'dQw4w9WgXcQ', 'start' => -5]))
        ->assertSessionHasErrors('start');
});
