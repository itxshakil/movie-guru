<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Maps an OMDb title response to MovieDetail attributes.
 */
final class OmdbDetailMapper
{
    /**
     * @param  array<string, mixed> $detail
     * @return array<string, mixed>
     */
    public function toAttributes(array $detail): array
    {
        return [
            'title' => $detail['Title'],
            'year' => $detail['Year'] ?? null,
            'release_date' => $detail['Released'] ?? null,
            'poster' => $detail['Poster'] ?? null,
            'type' => $detail['Type'] ?? null,
            'imdb_rating' => $this->isValue($detail['imdbRating'] ?? null) ? $detail['imdbRating'] : 0,
            'imdb_votes' => $this->isValue($detail['imdbVotes'] ?? null)
                ? str_replace(',', '', (string) $detail['imdbVotes'])
                : 0,
            'genre' => $detail['Genre'] ?? null,
            'director' => $this->truncate($detail['Director'] ?? null),
            'writer' => $this->truncate($detail['Writer'] ?? null),
            'actors' => $this->truncate($detail['Actors'] ?? null),
            'details' => $detail,
        ];
    }

    private function truncate(mixed $value): string
    {
        return mb_substr(is_scalar($value) ? (string) $value : '', 0, 255);
    }

    private function isValue(mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== 'N/A';
    }
}
