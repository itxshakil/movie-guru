<?php

declare(strict_types=1);

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

function guestItem(array $overrides = []): array
{
    return [
        'imdb_id' => 'tt1375666',
        'title' => 'Inception',
        'poster' => null,
        'year' => '2010',
        'status' => 'to_watch',
        'reaction' => null,
        'watched_at' => null,
        ...$overrides,
    ];
}

test('titles new to the account are added as sent', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [
            guestItem(),
            guestItem(['imdb_id' => 'tt0816692', 'title' => 'Interstellar', 'status' => 'watched', 'reaction' => 'liked', 'watched_at' => '2026-10-01T20:00:00+00:00']),
        ]])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $watched = LibraryItem::query()->forUser($user)->where('imdb_id', 'tt0816692')->sole();
    expect($watched->status)->toBe(LibraryStatus::Watched)
        ->and($watched->reaction)->toBe(LibraryReaction::Liked)
        ->and($watched->watched_at->toIso8601String())->toBe('2026-10-01T20:00:00+00:00');
});

test('watched on the device beats to watch in the account', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked', 'watched_at' => '2026-10-01T20:00:00+00:00'])]])
        ->assertSuccessful();

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)->and($item->reaction)->toBe(LibraryReaction::Liked);
});

test('the account reaction wins over the device reaction', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->disliked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->reaction)->toBe(LibraryReaction::Disliked);
});

test('a device reaction fills an account title that has none', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->watched()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched', 'reaction' => 'liked'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->reaction)->toBe(LibraryReaction::Liked);
});

test('to watch on the device never undoes watched in the account', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->liked()->create(['imdb_id' => 'tt1375666']);

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem()]])
        ->assertSuccessful();

    $item = LibraryItem::query()->forUser($user)->sole();
    expect($item->status)->toBe(LibraryStatus::Watched)->and($item->reaction)->toBe(LibraryReaction::Liked);
});

test('a watched title without a date gets one', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'watched'])]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->watched_at)->not->toBeNull();
});

test('a watched date with an offset is stored in the app timezone', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('library.import.store'), ['items' => [
            guestItem(['status' => 'watched', 'watched_at' => '2026-10-02T01:30:00+05:30']),
        ]])
        ->assertSuccessful();

    expect(LibraryItem::query()->forUser($user)->sole()->watched_at->toIso8601String())
        ->toBe('2026-10-01T20:00:00+00:00');
});

test('imported items are validated', function (): void {
    $this->actingAs(User::factory()->create())
        ->postJson(route('library.import.store'), ['items' => [guestItem(['status' => 'to_watch', 'reaction' => 'liked'])]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.reaction');
});

test('guests cannot import a library', function (): void {
    $this->postJson(route('library.import.store'), ['items' => [guestItem()]])->assertUnauthorized();
});
