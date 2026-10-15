<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Resolves Web Share Target payloads (shared text, titles or URLs) to an internal app URL.
 * It only ever returns URLs for named routes of this app, so it cannot become an open redirect.
 */
final readonly class SharedLinkResolver
{
    /**
     * Sites whose title URLs carry a slug but no IMDb id, mapped to the pattern that captures the slug.
     *
     * @var array<string, string>
     */
    private const array TITLE_SLUG_PATTERNS = [
        'letterboxd.com' => '#^/film/([^/]+)#',
        'rottentomatoes.com' => '#^/(?:m|tv)/([^/]+)#',
        'justwatch.com' => '#^/[a-z]{2}/(?:movie|tv-show|tv-series)/([^/]+)#',
    ];

    public function __construct(
        private ImdbIdExtractor $imdbIdExtractor,
    ) {}

    /**
     * @param array<int, string|null> $sharedValues
     */
    public function resolve(array $sharedValues): ?string
    {
        $sharedText = implode(' ', array_filter($sharedValues, static fn (?string $value): bool => filled($value)));

        if ($sharedText === '') {
            return null;
        }

        $imdbId = $this->imdbIdExtractor->extract($sharedText);
        if ($imdbId !== null) {
            return route('movie.show', ['imdbID' => $imdbId]);
        }

        preg_match_all('#https?://\S+#i', $sharedText, $matches);
        foreach ($matches[0] as $url) {
            $title = $this->titleFromUrl($url);
            if ($title !== null) {
                return route('search', ['s' => $title]);
            }
        }

        return null;
    }

    private function titleFromUrl(string $url): ?string
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        foreach (self::TITLE_SLUG_PATTERNS as $domain => $pattern) {
            $isDomainMatch = $host === $domain || str_ends_with($host, '.' . $domain);
            if (! $isDomainMatch || preg_match($pattern, $path, $matches) !== 1) {
                continue;
            }

            // "the-dark-knight-2008" and "the_dark_knight" both become "the dark knight".
            $title = preg_replace('/[-_]+(?:19|20)\d{2}$/', '', rawurldecode($matches[1]));
            $title = mb_trim((string) preg_replace('/[-_]+/', ' ', (string) $title));

            return $title === '' ? null : $title;
        }

        return null;
    }
}
