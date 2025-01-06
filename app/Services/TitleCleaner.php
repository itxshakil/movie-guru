<?php

namespace App\Services;

class TitleCleaner
{
    /**
     * Clean the movie title based on various patterns.
     *
     * @param string|null $title
     * @return string
     */
    public function clean(?string $title): string
    {
        if (is_null($title)) {
            return '';
        }

        $patterns = [
            '/\s*movie$/i',
            '/\s*south movie$/i',
            '/\s*full movie$/i',
            '/\s*\(\d{4}\)$/i',
            '/\b(?:filmyzilla|123movies|torrent|netflix|hotstar|prime video)\b/i',
            '/\b(?:part|episode|season)\s*\d+\b/i',
            '/\b(?:hindi|tamil|telugu|english|dubbed|subtitle|subtitles)\b/i',
            '/\b(?:hd|1080p|720p|480p|bluray|camrip|hdrip|webrip|dvdrip|ultrahd)\b/i',
            '/\b(?:new|latest|old)\s*\d{4}\b/i',
            '/\.\b(?:mp4|mkv|avi|mov|flv|wmv|webm)\b/i',
            '/\b(?:and|watch online|free|download|streaming)\b/i',
            '/\b(\w+)\s+\1\b/i',
        ];

        $cleanedTitle = $title;
        foreach ($patterns as $pattern) {
            $cleanedTitle = preg_replace($pattern, '', $cleanedTitle);
        }

        // Standardize whitespace and trim
        $cleanedTitle = preg_replace('/\s{2,}/', ' ', $cleanedTitle);
        $cleanedTitle = trim($cleanedTitle);

        // If nothing is left after cleaning, revert to original
        if (empty($cleanedTitle) || strlen($cleanedTitle) < 2) {
            return $title;
        }

        return $cleanedTitle;
    }
}
