<?php
declare(strict_types=1);

namespace App\DTOs;

class WatchModeSearchResult
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly string  $type,
        public readonly ?int    $year = null,
        public readonly ?string $imdbId = null,
        public readonly ?int    $tmdbId = null,
        public readonly ?string $tmdbType = null,
        public readonly ?string $mainProfession = null
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: $data['type'] ?? 'person',
            year: $data['year'] ?? null,
            imdbId: $data['imdb_id'] ?? null,
            tmdbId: $data['tmdb_id'] ?? null,
            tmdbType: $data['tmdb_type'] ?? null,
            mainProfession: $data['main_profession'] ?? null
        );
    }
}
