<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class WatchModeSearchResult
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public ?int $year = null,
        public ?string $imdbId = null,
        public ?int $tmdbId = null,
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
            id: (int)$data['id'],
            name: (string)$data['name'],
            type: (string)($data['type'] ?? 'person'),
            year: is_numeric($data['year']) ? (int)$data['year'] : null,
            imdbId: isset($data['imdb_id']) ? (string)$data['imdb_id'] : null,
            tmdbId: is_numeric($data['tmdb_id']) ? (int)$data['tmdb_id'] : null,
            tmdbType: isset($data['tmdb_type']) ? (string)$data['tmdb_type'] : null,
            mainProfession: isset($data['main_profession']) ? (string)$data['main_profession'] : null,
        );
    }
}
