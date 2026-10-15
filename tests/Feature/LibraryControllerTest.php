<?php

declare(strict_types=1);

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

function libraryPayload(array $overrides = []): array
{
    return [
        'title' => 'Inception',
        'poster' => 'https://example.com/inception.jpg',
        'year' => '2010',
        'status' => 'to_watch',
        'reaction' => null,
        ...$overrides,
    ];
}

test('members get their whole library as json', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);
    LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt0816692']);
    LibraryItem::factory()->create(['imdb_id' => 'tt0068646']);

    $this->actingAs($user)
        ->getJson(route('library.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['imdb_id', 'title', 'poster', 'year', 'status', 'reaction', 'watched_at', 'saved_at']]])
        ->assertJsonFragment(['imdb_id' => 'tt0816692', 'status' => 'watched', 'reaction' => 'liked']);
});

test('plain visits to the library open the watchlist page', function (): void {
    $this->actingAs(User::factory()->create())->get(route('library.index'))->assertRedirect(route('watchlist'));
});

test('guests cannot use the library api', function (): void {
    $this->getJson(route('library.index'))->assertUnauthorized();
    $this->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload())->assertUnauthorized();
    $this->deleteJson(route('library.destroy', ['imdbId' => 'tt1375666']))->assertUnauthorized();
});

test('saving a title adds it to the watchlist', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload())
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'to_watch')
        ->assertJsonPath('data.watched_at', null);

    $this->assertDatabaseHas('library_items', ['user_id' => $user->id, 'imdb_id' => 'tt1375666', 'status' => 'to_watch']);
});

test('marking a watchlist title watched moves it in place and stamps watched_at', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched', 'reaction' => 'liked']))
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'watched')
        ->assertJsonPath('data.reaction', 'liked');

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)
        ->and($item->reaction)->toBe(LibraryReaction::Liked)
        ->and($item->watched_at)->not->toBeNull();
});

test('changing the reaction keeps the original watched date', function (): void {
    $user = User::factory()->create();
    $item = LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666', 'watched_at' => '2026-01-02 03:04:05']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched', 'reaction' => 'disliked']))
        ->assertSuccessful();

    expect($item->refresh()->watched_at->toDateTimeString())->toBe('2026-01-02 03:04:05')
        ->and($item->reaction)->toBe(LibraryReaction::Disliked);
});

test('moving a watched title back to the watchlist clears its reaction and watched date', function (): void {
    $user = User::factory()->create();
    $item = LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'to_watch']))
        ->assertSuccessful();

    $item->refresh();
    expect($item->status)->toBe(LibraryStatus::ToWatch)
        ->and($item->reaction)->toBeNull()
        ->and($item->watched_at)->toBeNull();
});

test('saving the same title twice keeps one row', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched']));
    $this->actingAs($user)->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'watched']));

    expect(LibraryItem::query()->forUser($user)->count())->toBe(1);
});

test('a reaction needs the title to be watched', function (): void {
    $this->actingAs(User::factory()->create())
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), libraryPayload(['status' => 'to_watch', 'reaction' => 'liked']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('reaction');
});

test('library updates are validated', function (array $payload, string $invalidField): void {
    $this->actingAs(User::factory()->create())
        ->putJson(route('library.update', ['imdbId' => 'tt1375666']), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($invalidField);
})->with([
    'missing title' => [['status' => 'to_watch'], 'title'],
    'unknown status' => [['title' => 'Inception', 'status' => 'abandoned'], 'status'],
    'unknown reaction' => [['title' => 'Inception', 'status' => 'watched', 'reaction' => 'meh'], 'reaction'],
]);

test('only imdb ids are accepted in library urls', function (): void {
    $this->actingAs(User::factory()->create())
        ->putJson('/library/not-an-id', libraryPayload())
        ->assertNotFound();
});

test('members can remove only their own titles', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666']);
    LibraryItem::factory()->for($otherUser)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)->deleteJson(route('library.destroy', ['imdbId' => 'tt1375666']))->assertNoContent();

    $this->assertDatabaseMissing('library_items', ['user_id' => $user->id, 'imdb_id' => 'tt1375666']);
    $this->assertDatabaseHas('library_items', ['user_id' => $otherUser->id, 'imdb_id' => 'tt1375666']);
});
