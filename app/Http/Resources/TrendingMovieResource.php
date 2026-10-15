<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MovieDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MovieDetail
 */
final class TrendingMovieResource extends JsonResource
{
    /**
     * @return array{imdb_id: string, title: string, year: string|null, poster: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'imdb_id' => $this->imdb_id,
            'title' => $this->title,
            'year' => $this->year,
            'poster' => $this->poster,
        ];
    }
}
