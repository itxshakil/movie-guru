<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;

/**
 * Saves a title in a member's library and applies the library state rules:
 * becoming watched stamps watched_at (kept on later edits), a reaction only exists on
 * watched titles, and going back to "to_watch" clears both.
 */
final class UpdateLibraryItem
{
    /**
     * @param array{title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null} $attributes
     */
    public function handle(User $user, string $imdbId, array $attributes): LibraryItem
    {
        $item = LibraryItem::query()->firstOrNew(['user_id' => $user->id, 'imdb_id' => $imdbId]);
        $status = LibraryStatus::from($attributes['status']);
        $wasWatched = $item->exists && $item->status === LibraryStatus::Watched;

        $item->fill([
            'title' => $attributes['title'],
            'poster' => $attributes['poster'] ?? null,
            'year' => $attributes['year'] ?? null,
            'status' => $status,
        ]);

        if ($status === LibraryStatus::Watched) {
            $item->watched_at = $wasWatched ? $item->watched_at : now();
            $item->reaction = isset($attributes['reaction']) ? LibraryReaction::from($attributes['reaction']) : null;
        } else {
            $item->watched_at = null;
            $item->reaction = null;
        }

        $item->save();

        return $item;
    }
}
