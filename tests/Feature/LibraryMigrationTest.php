<?php

declare(strict_types=1);

use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function convertWatchlistsMigration(): object
{
    return require database_path('migrations/2026_10_15_000000_convert_watchlists_to_library_items.php');
}

test('existing watchlist rows become to watch library items', function (): void {
    $user = User::factory()->create();
    $migration = convertWatchlistsMigration();
    $migration->down();

    DB::table('watchlists')->insert([
        'user_id' => $user->id,
        'imdb_id' => 'tt0111161',
        'title' => 'The Shawshank Redemption',
        'poster' => null,
        'year' => '1994',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration->up();

    expect(Schema::hasTable('watchlists'))->toBeFalse();
    $item = LibraryItem::query()->sole();
    expect($item->imdb_id)->toBe('tt0111161')
        ->and($item->status)->toBe(LibraryStatus::ToWatch)
        ->and($item->reaction)->toBeNull()
        ->and($item->watched_at)->toBeNull();
});

test('a title can only be in a member library once', function (): void {
    $item = LibraryItem::factory()->create();

    expect(fn () => LibraryItem::factory()->create(['user_id' => $item->user_id, 'imdb_id' => $item->imdb_id]))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

test('library items scope to their member', function (): void {
    $user = User::factory()->create();
    LibraryItem::factory()->for($user)->create();
    LibraryItem::factory()->create();

    expect(LibraryItem::query()->forUser($user)->count())->toBe(1);
});

test('the old member watchlist url opens the watchlist page', function (): void {
    $this->get('/my-watchlist')->assertRedirect('/watchlist');
});
