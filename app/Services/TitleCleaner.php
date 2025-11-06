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

        // Trim leading and trailing spaces before processing
        $cleanedTitle = trim($title);

        // Define regex patterns for unwanted elements
        $patterns = [
            '/https?:\/\/\S+/i', // Remove URLs
            '/\b(share|shared|click|link|download|copy|view|terabox|mega\.nz|drive\.google)\b.*/i', // Remove share messages
            '/\s*full[\s-]*movie$/i',       // Remove "full movie" (with optional spaces or hyphen) at the end
            '/\s*south[\s-]*movie$/i',      // Remove "south movie" (with optional spaces or hyphen) at the end
            '/\s*movie$/i',                 // Remove "movie" at the end
            '/\s*\(\d{4}\)$/i',             // Remove years in parentheses (e.g., "(2023)")
            '/\b(?:filmyzilla|123movies|torrent|netflix|hotstar|prime video|hbo max)\b/i',
            // Remove platform names
            '/\b(?:part|episode|season)\s*\d+\b/i', // Remove parts, episodes, or seasons
            '/\b(?:hindi|tamil|telugu|english|dubbed|subtitle|subtitles|bengali|malayalam|urdu)\b/i',
            // Remove language or subtitle info
            '/\b(?:hd|uhd|4k|1080p|720p|480p|bluray|camrip|hdrip|webrip|dvdrip|ultrahd|vhs)\b/i',
            // Remove quality indicators
            '/\b(?:new|latest|old)\s*\d{4}\b/i', // Remove "new 2023", "old 1999" patterns
            '/\.\b(?:mp4|mkv|avi|mov|flv|wmv|webm|ts|mpeg|mpg)\b/i', // Remove file extensions
            '/\b(?:and|watch online|free|download|streaming|stream)\b/i',
            // Remove irrelevant keywords
            '/\b(?:bollywood|hollywood|tollywood|kollywood|mollywood|cinema|film industry|cinematography)\b/i',
            // Remove industry names
            '/\b(?:blockbuster|classic|remake|sequel|prequel|anthology|feature)\b/i',
            // Remove additional movie-related keywords
        ];

        // Apply regex patterns
        foreach ($patterns as $pattern) {
            $cleanedTitle = preg_replace($pattern, '', $cleanedTitle);
        }

        // Handle duplicate consecutive words
        $cleanedTitle = preg_replace('/\b(\w+)(?:\s+\1)+\b/i', '$1', $cleanedTitle);

        // Standardize whitespace and trim
        $cleanedTitle = preg_replace('/\s{2,}/', ' ', $cleanedTitle); // Reduce multiple spaces to one
        $cleanedTitle = trim($cleanedTitle); // Final trim after all cleaning

        // Fallback to original title if the result is empty or too short
        if (empty($cleanedTitle) || strlen($cleanedTitle) < 2) {
            return $title;
        }

        return $cleanedTitle;
    }
}
