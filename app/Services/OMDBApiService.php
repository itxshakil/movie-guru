<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\OmdbUnavailableException;
use App\OMDB\MovieType;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
        $this->apiKeys = explode(',', (string) config('omdb.api_keys'));
        $this->titleCleaner = resolve(TitleCleaner::class);

        throw_if($this->apiKeys === [], Exception::class, 'OMDB API keys are not configured.');
    }

    /**
     * @throws ConnectionException
     */
    public function getByTitle(string $title): mixed
    {
        return $this->makeRequest('t=' . $title);
    }

    /**
     * @throws ConnectionException
     */
    public function getById(?string $imdbID = null): mixed
    {
        $this->imdbID = $imdbID ?? $this->imdbID;

        $query = sprintf('i=%s&&plot=full', $this->imdbID);

        return $this->makeRequest($query);
    }

    /**
     * @param string|int|null $year
     *
     * @throws Exception
     */
    public function searchByTitle(?string $title = '', int $page = 1, ?string $movieType = null, $year = null): mixed
    {
        $this->title = $this->titleCleaner->clean($title ?? $this->title ?? '');
        $this->page = $page;
        $this->movieType = $movieType ?? $this->movieType;
        $this->year = $year ?? $this->year;

        if ($this->title === '') {
            return ['Response' => 'False', 'Error' => 'Movie not found!', 'Search' => [], 'totalResults' => '0'];
        }

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
    private function makeRequest(string $query): mixed
    {
        $apiKey = $this->getRandomApiKey();
        $url = 'https://www.omdbapi.com/?apikey=' . $apiKey . '&' . $query;
        $startTime = microtime(as_float: true);

        try {
            $response = Http::retry(3, 200, OmdbUnavailableException::isTransient(...))
                ->connectTimeout(3)
                ->timeout(10)
                ->get($url)
                ->json();
        } catch (ConnectionException|RequestException $exception) {
            $unavailable = OmdbUnavailableException::fromHttpFailure($exception);

            Log::channel('omdb')->warning($unavailable->getMessage(), ['query' => $query]);

            throw $unavailable;
        }

        $endTime = microtime(as_float: true);
        $responseTime = round(($endTime - $startTime) * 1000, 2);

        Log::channel('omdb')->info(
            sprintf('OMDB API took %s ms to respond. Query: %s', $responseTime, $query),
            [
                $response,
            ],
        );

        if (($response['Response'] ?? null) === 'True' && ($response['Search'] ?? []) !== []) {
            $result = array_map(static fn (array $item): array => [
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
