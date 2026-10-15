<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Watchlist;

test('members get their watchlist as json', function (): void {
    $user = User::factory()->create();
    Watchlist::factory()->for($user)->create(['imdb_id' => 'tt0111161', 'title' => 'The Shawshank Redemption']);
    Watchlist::factory()->create(['imdb_id' => 'tt0068646']);

    $this->actingAs($user)
        ->getJson(route('watchlist.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.imdb_id', 'tt0111161')
        ->assertJsonStructure(['data' => [['imdb_id', 'title', 'poster', 'year']]]);
});

test('visiting the old member watchlist url opens the shared watchlist page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('watchlist.index'))
        ->assertRedirect(route('watchlist'));
});

test('guests cannot read a member watchlist', function (): void {
    $this->getJson(route('watchlist.index'))->assertUnauthorized();
});

test('members can save a title', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('watchlist.store'), ['imdb_id' => 'tt0111161', 'title' => 'The Shawshank Redemption', 'year' => '1994'])
        ->assertSuccessful()
        ->assertJsonPath('saved', true);

    $this->assertDatabaseHas('watchlists', ['user_id' => $user->id, 'imdb_id' => 'tt0111161']);
});

test('members can remove only their own titles', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Watchlist::factory()->for($user)->create(['imdb_id' => 'tt0111161']);
    Watchlist::factory()->for($otherUser)->create(['imdb_id' => 'tt0111161']);

    $this->actingAs($user)
        ->deleteJson(route('watchlist.destroy', ['imdbId' => 'tt0111161']))
        ->assertSuccessful();

    $this->assertDatabaseMissing('watchlists', ['user_id' => $user->id, 'imdb_id' => 'tt0111161']);
    $this->assertDatabaseHas('watchlists', ['user_id' => $otherUser->id, 'imdb_id' => 'tt0111161']);
});

test('signing in merges the guest watchlist into the account without duplicates', function (): void {
    $user = User::factory()->create();
    Watchlist::factory()->for($user)->create(['imdb_id' => 'tt0111161', 'title' => 'Account copy']);

    $this->actingAs($user)
        ->postJson(route('watchlist.import.store'), [
            'items' => [
                ['imdb_id' => 'tt0111161', 'title' => 'Guest copy', 'poster' => null, 'year' => '1994'],
                ['imdb_id' => 'tt0068646', 'title' => 'The Godfather', 'poster' => 'https://example.com/p.jpg', 'year' => '1972'],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(Watchlist::query()->where('user_id', $user->id)->count())->toBe(2);
    $this->assertDatabaseHas('watchlists', ['user_id' => $user->id, 'imdb_id' => 'tt0111161', 'title' => 'Account copy']);
    $this->assertDatabaseHas('watchlists', ['user_id' => $user->id, 'imdb_id' => 'tt0068646', 'title' => 'The Godfather']);
});

test('merging validates every item', function (): void {
    $this->actingAs(User::factory()->create())
        ->postJson(route('watchlist.import.store'), ['items' => [['title' => 'Missing id']]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.imdb_id');
});

test('guests cannot merge a watchlist', function (): void {
    $this->postJson(route('watchlist.import.store'), ['items' => []])->assertUnauthorized();
});
