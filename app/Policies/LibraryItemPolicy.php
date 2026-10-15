<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LibraryItem;
use App\Models\User;

final class LibraryItemPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, LibraryItem $libraryItem): bool
    {
        return $user->id === $libraryItem->user_id;
    }

    public function delete(User $user, LibraryItem $libraryItem): bool
    {
        return $user->id === $libraryItem->user_id;
    }
}
