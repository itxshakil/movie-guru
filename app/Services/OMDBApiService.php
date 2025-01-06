<?php

namespace App\Services;

use App\OMDB\MovieType;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OMDBApiService
{
    protected const TYPE_SEARCH = 's';
    protected const TYPE_ID = 'i';

    protected string $apiKey;
    protected string $type;
    private int $page;
    // TODO: make it clear that it can be always MovieType and cast accordingly
    private MovieType|string|null $movieType;
    private ?int $year;
    private string $title;
    private string $imdbID;

    public function __construct()
    {
        $this->apiKey = config('omdb.api_key');
    }

    public function getByTitle(string $title)
    {
        return $this->makeRequest('t=' . $title);
    }

    public function getById(string $imdbID = '')
    {
        $this->type = self::TYPE_ID;

        $this->imdbID = $imdbID ?? $this->imdbID;

        $query = "i=$this->imdbID&&plot=full";
        return $this->makeRequest($query);
    }

    private function cleanMovieTitle(?string $title): string
    {
        if(is_null($title)){
            return '';
        }

        $patterns = [
            '/\s*movie$/i',                          // Remove "movie" at the end
            '/\s*south movie$/i',                    // Remove "south movie" at the end
            '/\s*full movie$/i',                     // Remove "full movie" at the end
            '/\s*\(\d{4}\)$/i',                      // Remove "(YYYY)" at the end
            '/\b(?:filmyzilla|123movies|torrent|netflix|hotstar|prime video)\b/i', // Remove website mentions
            '/\b(?:part|episode|season)\s*\d+\b/i',  // Remove "Part 2" or "Episode 3"
            '/\b(?:hindi|tamil|telugu|english|dubbed|subtitle|subtitles)\b/i',     // Remove language tags
            '/\b(?:hd|1080p|720p|480p|bluray|camrip|hdrip|webrip|dvdrip|ultrahd)\b/i', // Remove quality descriptions
            '/\b(?:new|latest|old)\s*\d{4}\b/i',     // Remove "new 2023"
            '/\.\b(?:mp4|mkv|avi|mov|flv|wmv|webm)\b/i', // Remove file extensions
            '/\b(?:and|watch online|free|download|streaming)\b/i', // Remove superfluous connectors
            '/\b(\w+)\s+\1\b/i',                     // Remove duplicate words
//            '/[!@#$%^&*()_+={}\[\]:;"\'<>,.?~`|\\\/-\]+/', // Remove special characters
        ];

        // Apply patterns to clean title
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


    /**
     * @throws Exception
     */
    public function searchByTitle(?string $title = '',  int $page = 1, string $movieType = null, $year = null)
    {
        $this->type = self::TYPE_SEARCH;
        $this->title = $this->cleanMovieTitle($title ?? $this->title ?? '');
        $this->page = $page ?? $this->page ?? 1;
        $this->movieType = $movieType ?? $this->movieType ?? null;
        $this->year = $year ?? $this->year ?? null;

        $query = $this->generateSearchQuery();

        return $this->makeRequest($query);
    }

    protected function makeRequest(string $query)
    {
        $url = 'https://www.omdbapi.com/?apikey=' . $this->apiKey . '&' . $query;
        $startTime = microtime(true);
        $response =  Http::get($url)->json();

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        // TODO: Different level of log to check response time
        Log::channel('omdb')->info("'OMDB API took $responseTime ms to respond $url");

        return $response;
    }

    /**
     * Generate search query for OMDB API
     *
     * @return string
     * @throws Exception
     */
    public function generateSearchQuery(): string
    {
        $title = $this->title ?? null;

        if(!$title) {
            throw new Exception('Title is required');
        }

        $query = 's=' . $title;
        if ($this->page) {
            $query .= '&page=' . $this->page;
        }

        if ($this->movieType) {
            $query .= '&type=' . $this->movieType;
        }

        if ($this->year) {
            $query .= '&y=' . $this->year;
        }

        return $query;
    }
}
