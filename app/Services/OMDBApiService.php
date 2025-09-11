<?php

namespace App\Services;

use App\OMDB\MovieType;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OMDBApiService
{
    protected const string TYPE_SEARCH = 's';

    protected const string TYPE_ID = 'i';

    protected array $apiKeys;

    protected string $type;

    private int $page;

    private MovieType|string|null $movieType;

    private ?int $year;

    private string $title;

    private string $imdbID;

    private TitleCleaner $titleCleaner;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->apiKeys = explode(',', config('omdb.api_keys'));
        $this->titleCleaner = app(TitleCleaner::class);

        if (empty($this->apiKeys)) {
            throw new Exception('OMDB API keys are not configured.');
        }
    }

    public function getByTitle(string $title)
    {
        return $this->makeRequest('t='.$title);
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
    public function searchByTitle(?string $title = '', int $page = 1, ?string $movieType = null, $year = null)
    {
        $this->type = self::TYPE_SEARCH;
        $this->title = $this->titleCleaner->clean($title ?? $this->title ?? '');
        $this->page = $page ?? $this->page ?? 1;
        $this->movieType = $movieType ?? $this->movieType ?? null;
        $this->year = $year ?? $this->year ?? null;

        $query = $this->generateSearchQuery();

        return $this->makeRequest($query);
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    protected function makeRequest(string $query)
    {
        $apiKey = $this->getRandomApiKey();
        $url = 'https://www.omdbapi.com/?apikey='.$apiKey.'&'.$query;
        $startTime = microtime(true);
        $response = Http::connectTimeout(3)->get($url)->json();

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        Log::channel('omdb')->info("OMDB API took $responseTime ms to respond. URL: $url", [
            $response
        ]);

        if (
            isset($response['Response'])
            && $response['Response'] === 'True'
            && isset($response['Search'])
            && count($response['Search']) > 0
        ) {
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
     * Get a random API key from the configured keys
     *
     * @throws Exception
     */
    protected function getRandomApiKey(): string
    {
        if (empty($this->apiKeys)) {
            throw new Exception('No API keys are configured.');
        }

        return $this->apiKeys[array_rand($this->apiKeys)];
    }

    /**
     * Generate search query for OMDB API
     *
     * @throws Exception
     */
    public function generateSearchQuery(): string
    {
        $title = $this->title ?? null;

        if (!$title) {
            throw new Exception('Title is required');
        }

        $query = 's='.$title;
        if ($this->page) {
            $query .= '&page='.$this->page;
        }

        if ($this->movieType) {
            $query .= '&type='.$this->movieType;
        }

        if ($this->year) {
            $query .= '&y='.$this->year;
        }

        return $query;
    }
}
