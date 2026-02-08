<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\ShowPageAnalytics;
use App\Services\BotDetectorService;
use App\Services\OMDBApiService;
use App\Services\WatchModeService;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class DetailController extends Controller
{
    /**
     * @throws Throwable
     */
    public function show(Request $request, string $imdbId): Response|JsonResponse
    {
        $cleanImdbId = $this->extractImdbId($imdbId);

        throw_unless($cleanImdbId, NotFoundHttpException::class, 'Invalid IMDb ID. Provided');

        $cacheKey = 'detail.' . $imdbId;
        $cacheTTl = [now()->addHours(18), now()->addHours(24)];
        $movie = Cache::flexible($cacheKey, $cacheTTl, fn(): mixed => $this->fetchDetail($imdbId));

        $detail = $movie instanceof MovieDetail ? $movie->details : $movie;
        $affiliateLink = $movie instanceof MovieDetail ? $movie->affiliate_link : null;

        $sources = $movie->sources ?? [];

        $shouldRefresh =
            $movie instanceof MovieDetail
            && (
                empty($sources)
                || $movie->source_last_fetched_at === null
                || $movie->source_last_fetched_at->lt(now()->subMonth())
            );

        $botDetector = resolve(BotDetectorService::class);
        if ($botDetector->isBot($request) === false) {
            defer(static function () use ($shouldRefresh, $request, $imdbId): void {
                ShowPageAnalytics::create([
                    'imdb_id' => $imdbId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                if ($shouldRefresh) {
                    $watchMode = resolve(WatchModeService::class);
                    $sources = $watchMode->getTitleSources($imdbId, ['IN']);

                    if ($sources->isNotEmpty()) {
                        $movie = MovieDetail::where('imdb_id', $imdbId)->first();
                        if (!$movie) {
                            return;
                        }

                        $movie->update([
                            'sources' => $sources,
                            'source_last_fetched_at' => now(),
                        ]);
                    }
                }
            });
        }

        if ($request->wantsJson()) {
            return response()->json([
                'detail' => $detail,
                'sources' => $sources,
                'affiliateLink' => $affiliateLink,
            ]);
        }

        $recentlyReleasedMovies = Cache::remember(
            'recently-released-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::recentlyReleased()
                ->inRandomOrder()
                ->take(6)
                ->get([
                    'imdb_id',
                    'title',
                    'year',
                    'release_date',
                    'poster',
                    'type',
                    'imdb_rating',
                    'imdb_votes',
                    'director',
                    'writer',
                    'actors',
                ]),
        );

        $recommendedMovies = Cache::remember(
            'recommended-movies',
            now()->endOfDay(),
            static fn() => MovieDetail::recommended()
                ->inRandomOrder()
                ->take(6)
                ->get([
                    'imdb_id',
                    'title',
                    'year',
                    'release_date',
                    'poster',
                    'type',
                    'imdb_rating',
                    'imdb_votes',
                    'director',
                    'writer',
                    'actors',
                ]),
        );

        return Inertia::render('Show', [
            'detail' => $detail,
            'sources' => $sources,
            'affiliateLink' => $affiliateLink,
            'recentlyReleasedMovies' => $recentlyReleasedMovies,
            'recommendedMovies' => $recommendedMovies,
        ]);
    }

    /**
     * @return array|mixed
     *
     * @throws ConnectionException
     */
    public function fetchDetail(string $imdbId): mixed
    {
        $movie = MovieDetail::where('imdb_id', $imdbId)->first();

        if ($movie) {
            return $this->handleDB($movie, $imdbId);
        }

        return $this->fetchFromAPI($imdbId);
    }

    public function handleDB(MovieDetail $movie, string $imdbId): MovieDetail
    {
        $ipAddress = request()->ip();
        defer(static fn() => $movie->incrementViews($ipAddress));
        $this->updateDetailInBG($imdbId);

        return $movie;
    }

    /**
     * @return array|mixed
     *
     * @throws ConnectionException
     */
    public function fetchFromAPI(string $imdbId): mixed
    {
        $imdbId = $this->extractImdbId($imdbId);
        if (!$imdbId) {
            return [];
        }

        $OMDBApiService = resolve(OMDBApiService::class);
        $detail = $OMDBApiService->getById($imdbId);
        $ipAddress = request()->ip();

        defer(static function () use ($ipAddress, $detail, $imdbId): void {
            if ($detail === null || !isset($detail['Title'])) {
                $exception = new Exception('Failed to fetch details for IMDB ID: ' . $imdbId, 500);
                report($exception);

                Log::error('Failed to fetch details for IMDB ID: ' . $imdbId, [
                    'response' => $detail,
                ]);
            }

            $voted = $detail['imdbVotes'] && $detail['imdbVotes'] !== 'N/A'
                ? str_replace(',', '', $detail['imdbVotes'])
                : 0;

            MovieDetail::updateOrCreate([
                'imdb_id' => $imdbId,
            ], [
                'title' => $detail['Title'],
                'year' => $detail['Year'],
                'release_date' => $detail['Released'],
                'poster' => $detail['Poster'],
                'type' => $detail['Type'],
                'imdb_rating' => $detail['imdbRating'] && $detail['imdbRating'] !== 'N/A' ? $detail['imdbRating'] : 0,
                'imdb_votes' => $voted,
                'details' => $detail,
            ])->incrementViews($ipAddress);
        });

        return $detail; // Return fetched details
    }

    private function updateDetailInBG(string $imdbId): void
    {
        defer(
        /**
         * @throws ConnectionException
         */
            function () use ($imdbId): void {
                $OMDBApiService = resolve(OMDBApiService::class);
                $imdbId = $this->extractImdbId($imdbId);
                $updatedDetail = $OMDBApiService->getById($imdbId);

                if ($updatedDetail === null || !isset($updatedDetail['Title'])) {
                    $response = $updatedDetail['Response'] ?? null;
                    report(new Exception(
                        'Failed to fetch updated details for IMDB ID: ' . $imdbId . ' Response: ' . $response,
                        500,
                    ));

                    Log::error('Failed to fetch updated details for IMDB ID: ' . $imdbId, [
                        'response' => $updatedDetail,
                    ]);

                    return;
                }

                MovieDetail::updateOrCreate([
                    'imdb_id' => $imdbId,
                ], [
                    'title' => $updatedDetail['Title'],
                    'year' => $updatedDetail['Year'],
                    'release_date' => $updatedDetail['Released'],
                    'poster' => $updatedDetail['Poster'],
                    'type' => $updatedDetail['Type'],
                    'imdb_rating' => $this->isValue($updatedDetail['imdbRating']) ? $updatedDetail['imdbRating'] : 0,
                    'imdb_votes' => $this->isValue($updatedDetail['imdbVotes'] ?? null)
                        ? str_replace(
                            ',',
                            '',
                            $updatedDetail['imdbVotes'],
                        ) : 0,
                    'genre' => $updatedDetail['Genre'],
                    'director' => mb_substr((string)$updatedDetail['Director'], 0, 255),
                    'writer' => mb_substr((string)$updatedDetail['Writer'], 0, 255),
                    'actors' => mb_substr((string)$updatedDetail['Actors'], 0, 255),
                    'details' => $updatedDetail,
                ]);
            },
        );
    }

    private function isValue(mixed $value): bool
    {
        return $value && $value !== 'N/A';
    }

    /**
     * Extracts and returns a clean IMDb title ID (e.g., "tt6468322") from messy input.
     *
     * Handles:
     * - Raw IDs ("tt6468322")
     * - Full URLs ("https://www.imdb.com/title/tt6468322/?ref_=nv_sr_sr_1")
     * - Query strings and trackers ("tt6468322&sa=U&ved=...")
     * - HTML entities (&amp;)
     *
     * Returns: string|null
     */
    private function extractImdbId(string $input): ?string
    {
        $candidate = mb_trim($input);

        $candidate = html_entity_decode($candidate, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // - Starts with "tt"
        // - Followed by 7 or 8 digits (IMDb IDs are commonly 7–8 digits; allow 7+ to be safe)
        // - Bounded to avoid partial matches inside larger tokens
        $pattern = '/\btt\d{7,8}\b/i';

        if (preg_match($pattern, $candidate, $matches)) {
            return Str::lower($matches[0]);
        }

        return null;
    }
}
