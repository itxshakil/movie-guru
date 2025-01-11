<?php

declare(strict_types=1);

namespace App\Services;

class TitleCleaner
{
    /**
     * Clean the movie title based on various patterns.
     */
    public function clean(?string $title): string
    {
        if (is_null($title)) {
            return '';
        }

        // Define regex patterns for unwanted elements
        $patterns = [
            '/\s*movie$/i',
            // Remove "movie" at the end
            '/\s*south movie$/i',
            // Remove "south movie" at the end
            '/\s*full movie$/i',
            // Remove "full movie" at the end
            '/\s*\(\d{4}\)$/i',
            // Remove years in parentheses (e.g., "(2023)")
            '/\b(?:filmyzilla|123movies|torrent|netflix|hotstar|prime video)\b/i',
            // Remove platform names
            '/\b(?:part|episode|season)\s*\d+\b/i',
            // Remove parts, episodes, or seasons
            '/\b(?:hindi|tamil|telugu|english|dubbed|subtitle|subtitles)\b/i',
            // Remove language or subtitle info
            '/\b(?:hd|1080p|720p|480p|bluray|camrip|hdrip|webrip|dvdrip|ultrahd)\b/i',
            // Remove quality indicators
            '/\b(?:new|latest|old)\s*\d{4}\b/i',
            // Remove "new 2023", "old 1999" patterns
            '/\.\b(?:mp4|mkv|avi|mov|flv|wmv|webm)\b/i',
            // Remove file extensions
            '/\b(?:and|watch online|free|download|streaming)\b/i',
            // Remove irrelevant keywords
            '/\b(?:bollywood|hollywood|tollywood|kollywood|mollywood|cinema|film industry)\b/i',
            // Remove industry names
            '/\b(?:blockbuster|classic|remake|sequel|prequel)\b/i',
            // Remove additional movie-related keywords
            '/\b(\w+)\s+\1\b/i',
            // Remove duplicate consecutive words
        ];

        $cleanedTitle = $title;

        // Apply regex patterns
        foreach ($patterns as $pattern) {
            $cleanedTitle = preg_replace($pattern, '', $cleanedTitle);
        }

        // Standardize whitespace and trim
        $cleanedTitle = preg_replace('/\s{2,}/', ' ', $cleanedTitle);
        $cleanedTitle = trim($cleanedTitle);

        // Fallback to original title if the result is empty or too short
        if (empty($cleanedTitle) || strlen($cleanedTitle) < 2) {
            return $title;
        }

        return $cleanedTitle;
    }
}
