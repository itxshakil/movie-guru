<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Resolves web+movieguru URLs (e.g. "web+movieguru://i/tt0111161" or "web+movieguru://search/batman")
 * to an internal app URL. Only allow-listed shapes are recognised and everything else falls back to
 * the home page, so it cannot become an open redirect.
 */
final readonly class ProtocolLinkResolver
{
    private const string PROTOCOL_PREFIX_PATTERN = '#^web\+movieguru:(?://)?#i';

    public function __construct(
        private ImdbIdExtractor $imdbIdExtractor,
    ) {}

    public function resolve(string $protocolUrl): string
    {
        $protocolUrl = mb_trim($protocolUrl);

        if (preg_match(self::PROTOCOL_PREFIX_PATTERN, $protocolUrl) !== 1) {
            return route('home');
        }

        $path = mb_ltrim((string) preg_replace(self::PROTOCOL_PREFIX_PATTERN, '', $protocolUrl), '/');

        $imdbId = $this->imdbIdExtractor->extract($path);
        if ($imdbId !== null) {
            return route('movie.show', ['imdbID' => $imdbId]);
        }

        return $this->resolveSearch($path) ?? $this->resolvePage($path);
    }

    private function resolveSearch(string $path): ?string
    {
        if (preg_match('#^search(?:/([^?]+)|\?(?:.*&)?s=([^&]+))#i', $path, $matches) !== 1) {
            return null;
        }

        $encodedQuery = $matches[1] !== '' ? $matches[1] : $matches[2];
        $query = mb_trim(rawurldecode(str_replace('+', ' ', $encodedQuery)));

        return $query === '' ? route('search') : route('search', ['s' => $query]);
    }

    private function resolvePage(string $path): string
    {
        if (preg_match('#^mood/([a-z0-9-]+)/?$#i', $path, $matches) === 1) {
            return route('mood.show', ['mood' => Str::lower($matches[1])]);
        }

        if (preg_match('#^movie-match/([A-Za-z0-9]+)/?$#', $path, $matches) === 1) {
            return route('movie-match.show', ['token' => $matches[1]]);
        }

        return match (Str::lower(mb_rtrim($path, '/'))) {
            'watchlist' => route('watchlist'),
            'mood' => route('mood.index'),
            'movie-match' => route('movie-match.create'),
            default => route('home'),
        };
    }
}
