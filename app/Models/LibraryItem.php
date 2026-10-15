<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LibraryReaction;
use App\Enums\LibraryStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A title in a member's library: on their watchlist ("to_watch") or already watched.
 *
 * @property LibraryStatus        $status
 * @property LibraryReaction|null $reaction
 * @property Carbon|null          $watched_at
 */
final class LibraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'imdb_id',
        'title',
        'poster',
        'year',
        'status',
        'reaction',
        'watched_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Builder<self> $query
     */
    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }

    protected function casts(): array
    {
        return [
            'status' => LibraryStatus::class,
            'reaction' => LibraryReaction::class,
            'watched_at' => 'datetime',
        ];
    }
}
