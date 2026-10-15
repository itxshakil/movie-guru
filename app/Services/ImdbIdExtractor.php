<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class ImdbIdExtractor
{
    /**
     * Extracts a clean IMDb title id (e.g. "tt6468322") from messy input such as raw ids,
     * full IMDb URLs with trackers, shared text, or HTML-encoded strings.
     */
    public function extract(string $input): ?string
    {
        $candidate = html_entity_decode(mb_trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // IMDb ids are "tt" followed by 7 or 8 digits, bounded to avoid partial matches inside larger tokens.
        if (preg_match('/\btt\d{7,8}\b/i', $candidate, $matches) === 1) {
            return Str::lower($matches[0]);
        }

        return null;
    }
}
