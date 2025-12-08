<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class WatchModeSearchResult
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $type,
        public ?int   $year = null,
        public ?string $imdbId = null,
        public ?int   $tmdbId = null,
        public ?string $tmdbType = null,
        public ?string $mainProfession = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
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
            mainProfession: $data['main_profession'] ?? null,
        );
    }
}
