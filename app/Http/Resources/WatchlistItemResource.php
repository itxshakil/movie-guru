<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Watchlist
 */
final class WatchlistItemResource extends JsonResource
{
    /**
     * @return array{imdb_id: string, title: string, poster: string|null, year: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'imdb_id' => $this->imdb_id,
            'title' => $this->title,
            'poster' => $this->poster,
            'year' => $this->year,
        ];
    }
}
