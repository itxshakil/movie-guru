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
    private TitleCleaner $titleCleaner;

    public function __construct()
    {
        $this->apiKey = config('omdb.api_key');
        $this->titleCleaner = app(TitleCleaner::class);
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


    /**
     * @throws Exception
     */
    public function searchByTitle(?string $title = '',  int $page = 1, string $movieType = null, $year = null)
    {
        $this->type = self::TYPE_SEARCH;
        $this->title = $this->titleCleaner->clean($title ?? $this->title ?? '');
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

        if (isset($response['Response']) && $response['Response'] === 'True' && isset($response['Search']) && count(
                $response['Search']
            ) > 0) {
            $result = array_map(function ($item) {
                return [
                    'title' => $item['Title'],
                    'year' => $item['Year'],
                    'type' => $item['Type'],
                    'imdb_id' => $item['imdbID'],
                    'poster' => $item['Poster'],
                ];
            }, $response['Search']);

            $response['Search'] = $result;
        }

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
