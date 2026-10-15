<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Support\Facades\DB;

/**
 * Merges a guest's on-device watchlist into their account after they sign in.
 * Titles already in the account are kept as they are.
 */
final class MergeGuestWatchlist
{
    /**
     * @param array<int, array{imdb_id: string, title: string, poster?: string|null, year?: string|null}> $items
     */
    public function handle(User $user, array $items): void
    {
        DB::transaction(static function () use ($user, $items): void {
            foreach ($items as $item) {
                Watchlist::query()->firstOrCreate(
                    ['user_id' => $user->id, 'imdb_id' => $item['imdb_id']],
                    ['title' => $item['title'], 'poster' => $item['poster'] ?? null, 'year' => $item['year'] ?? null],
                );
            }
        });
    }
}
