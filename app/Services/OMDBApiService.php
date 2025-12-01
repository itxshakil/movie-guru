<?php

declare(strict_types=1);

namespace App\Services;

use App\OMDB\MovieType;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class OMDBApiService
{
    /**
     * @var mixed[]
     */
    private array $apiKeys;

    private int $page;

    private MovieType|string|null $movieType = null;

    private ?int $year = null;

    private string $title;

    private string $imdbID;

    private readonly TitleCleaner $titleCleaner;

    /**
     * @throws Exception|Throwable
     */
    public function __construct()
    {
        $this->apiKeys = explode(',', (string)config('omdb.api_keys'));
        $this->titleCleaner = app(TitleCleaner::class);

        throw_if($this->apiKeys === [], Exception::class, 'OMDB API keys are not configured.');
    }

    /**
     * @throws ConnectionException
     */
    public function getByTitle(string $title)
    {
        return $this->makeRequest('t=' . $title);
    }

    /**
     * @throws ConnectionException
     */
    public function getById(?string $imdbID = null)
    {
        $this->imdbID = $imdbID ?? $this->imdbID;

        $query = sprintf('i=%s&&plot=full', $this->imdbID);

        return $this->makeRequest($query);
    }

    /**
     * @throws Exception
     */
    public function searchByTitle(?string $title = '', int $page = 1, ?string $movieType = null, $year = null)
    {
        $this->title = $this->titleCleaner->clean($title ?? $this->title ?? '');
        $this->page = $page;
        $this->movieType = $movieType ?? $this->movieType ?? null;
        $this->year = $year ?? $this->year ?? null;

        $query = $this->generateSearchQuery();

        return $this->makeRequest($query);
    }

    /**
     * Generate search query for OMDB API.
     *
     * @throws Exception
     */
    public function generateSearchQuery(): string
    {
        $title = $this->title ?? null;

        throw_unless($title, Exception::class, 'Title is required');

        $query = 's=' . $title;
        if ($this->page !== 0) {
            $query .= '&page=' . $this->page;
        }

        if ($this->movieType !== null) {
            $query .= '&type=' . $this->movieType;
        }

        if ($this->year) {
            $query .= '&y=' . $this->year;
        }

        return $query;
    }

    /**
     * @throws ConnectionException
     * @throws Exception
     */
    private function makeRequest(string $query)
    {
        $apiKey = $this->getRandomApiKey();
        $url = 'https://www.omdbapi.com/?apikey=' . $apiKey . '&' . $query;
        $startTime = microtime(true);
        $response = Http::connectTimeout(3)->get($url)->json();

        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        Log::channel('omdb')->info(sprintf('OMDB API took %s ms to respond. URL: %s', $responseTime, $url), [
            $response,
        ]);

        if (
            isset($response['Response'], $response['Search'])
            && $response['Response'] === 'True'
            && count($response['Search']) > 0
        ) {
            $result = array_map(fn(array $item): array => [
                'title' => $item['Title'],
                'year' => $item['Year'],
                'type' => $item['Type'],
                'imdb_id' => $item['imdbID'],
                'poster' => $item['Poster'],
            ], $response['Search']);

            $response['Search'] = $result;
        }

        return $response;
    }

    /**
     * Get a random API key from the configured keys.
     *
     * @throws Exception|Throwable
     */
    private function getRandomApiKey(): string
    {
        throw_if($this->apiKeys === [], Exception::class, 'No API keys are configured.');

        return $this->apiKeys[array_rand($this->apiKeys)];
    }
}
