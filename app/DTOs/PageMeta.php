<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Head tags rendered on the server, so crawlers and link previews (WhatsApp, X, Facebook) see them without running JS.
 * Show.vue receives the same values as a prop, so the two never drift apart.
 */
final readonly class PageMeta
{
    private const string FALLBACK_TITLE = 'MovieGuru - Find Your Next Favorite';

    private const string FALLBACK_DESCRIPTION = 'Explore a vast library of movies and series. Find details, ratings, and where to watch your favorites.';

    private const string FALLBACK_IMAGE = '/assets/images/no-poster.jpg';

    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public string $image,
    ) {}

    /**
     * @param array<string, mixed>|null $detail OMDb fields (Title, Year, Genre, Actors, Plot, Poster)
     */
    public static function forTitle(?array $detail, string $canonical): self
    {
        $title = self::field($detail, 'Title');
        $year = self::field($detail, 'Year');
        $label = $year === '' ? $title : "{$title} ({$year})";

        return new self(
            title: $title === '' ? self::FALLBACK_TITLE : "Watch {$label} Online: Cast, Plot & Info",
            description: self::describe($detail, $label),
            canonical: $canonical,
            image: self::poster($detail),
        );
    }

    /**
     * @param array<string, mixed>|null $detail
     */
    private static function describe(?array $detail, string $label): string
    {
        $plot = self::field($detail, 'Plot');

        if ($label === '' || $plot === '') {
            return self::FALLBACK_DESCRIPTION;
        }

        $genre = self::field($detail, 'Genre');
        $actors = self::field($detail, 'Actors');
        $genrePart = $genre === '' ? '' : 'Genre: ' . explode(', ', $genre)[0] . '. ';
        $castPart = $actors === '' ? '' : 'Starring: ' . implode(', ', array_slice(explode(', ', $actors), 0, 2)) . '. ';
        $plotPart = mb_strlen($plot) > 100 ? mb_substr($plot, 0, 100) . '...' : $plot;

        return "{$label}: {$genrePart}{$castPart}{$plotPart} Find streaming options and detailed information on MovieGuru.";
    }

    /**
     * @param array<string, mixed>|null $detail
     */
    private static function poster(?array $detail): string
    {
        $poster = self::field($detail, 'Poster');

        return $poster === '' ? url(self::FALLBACK_IMAGE) : $poster;
    }

    /**
     * OMDb writes "N/A" for any field it has no value for.
     *
     * @param array<string, mixed>|null $detail
     */
    private static function field(?array $detail, string $key): string
    {
        $value = $detail[$key] ?? '';
        $value = is_string($value) ? mb_trim($value) : '';

        return $value === 'N/A' ? '' : $value;
    }
}
