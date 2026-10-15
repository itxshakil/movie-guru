<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LibraryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LibraryItem
 */
final class LibraryItemResource extends JsonResource
{
    /**
     * @return array{imdb_id: string, title: string, poster: string|null, year: string|null, status: string, reaction: string|null, watched_at: string|null, saved_at: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'imdb_id' => $this->imdb_id,
            'title' => $this->title,
            'poster' => $this->poster,
            'year' => $this->year,
            'status' => $this->status->value,
            'reaction' => $this->reaction?->value,
            'watched_at' => $this->watched_at?->toIso8601String(),
            'saved_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
