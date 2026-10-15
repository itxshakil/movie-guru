<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Watchlist;

final class WatchlistPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function create(): bool
    {
        return true;
    }

    public function delete(User $user, Watchlist $watchlist): bool
    {
        return $user->id === $watchlist->user_id;
    }
}
