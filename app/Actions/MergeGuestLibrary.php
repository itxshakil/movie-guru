<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Merges a guest's on-device library into their account after they sign in.
 * Watched beats to watch, and an existing account reaction is never overwritten.
 */
final class MergeGuestLibrary
{
    /**
     * @param array<int, array{imdb_id: string, title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null, watched_at?: string|null}> $items
     */
    public function handle(User $user, array $items): void
    {
        DB::transaction(function () use ($user, $items): void {
            foreach ($items as $item) {
                $this->mergeItem($user, $item);
            }
        });
    }

    /**
     * @param array{imdb_id: string, title: string, poster?: string|null, year?: string|null, status: string, reaction?: string|null, watched_at?: string|null} $item
     */
    private function mergeItem(User $user, array $item): void
    {
        $isWatched = LibraryStatus::from($item['status']) === LibraryStatus::Watched;
        $reaction = $isWatched && isset($item['reaction']) ? LibraryReaction::from($item['reaction']) : null;
        $watchedAt = $isWatched ? $this->resolveWatchedAt($item['watched_at'] ?? null) : null;

        $existing = LibraryItem::query()->forUser($user)->where('imdb_id', $item['imdb_id'])->first();

        if ($existing === null) {
            LibraryItem::query()->create([
                'user_id' => $user->id,
                'imdb_id' => $item['imdb_id'],
                'title' => $item['title'],
                'poster' => $item['poster'] ?? null,
                'year' => $item['year'] ?? null,
                'status' => $isWatched ? LibraryStatus::Watched : LibraryStatus::ToWatch,
                'reaction' => $reaction,
                'watched_at' => $watchedAt,
            ]);

            return;
        }

        if (! $isWatched) {
            return;
        }

        if ($existing->status === LibraryStatus::ToWatch) {
            $existing->update(['status' => LibraryStatus::Watched, 'reaction' => $reaction, 'watched_at' => $watchedAt]);

            return;
        }

        if ($existing->reaction === null && $reaction !== null) {
            $existing->update(['reaction' => $reaction]);
        }
    }

    /**
     * Eloquent stores wall-clock time without converting offsets, so normalise to the app timezone first.
     */
    private function resolveWatchedAt(?string $watchedAt): Carbon
    {
        if ($watchedAt === null) {
            return now();
        }

        return Carbon::parse($watchedAt)->setTimezone(config('app.timezone'));
    }
}
