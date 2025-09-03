<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\ShowPageAnalytics;
use App\Services\BotDetectorService;
use App\Services\OMDBApiService;
use App\Services\WatchModeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DetailController extends Controller
{
    public function show(Request $request, string $imdbId)
    {
        $cacheKey = 'detail.'.$imdbId;
        $cacheTTl = [now()->addHours(18), now()->addHours(24)];
        $movie = Cache::flexible($cacheKey, $cacheTTl, function () use ($imdbId) {
            return $this->fetchDetail($imdbId);
        });

        $detail = $movie instanceof MovieDetail ? $movie->details : $movie;

        $sources = $movie->sources ?? [];

        $shouldRefresh = $movie instanceof MovieDetail && (
                empty($sources) ||
                !$movie->source_last_fetched_at ||
                $movie->source_last_fetched_at->lt(now()->subDays(7))
            );

        $botDetector = app(BotDetectorService::class);
        if ($botDetector->isBot($request) === false) {
            defer(function () use ($shouldRefresh, $request, $imdbId) {
                ShowPageAnalytics::create([
                    'imdb_id' => $imdbId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                if ($shouldRefresh) {
                    $watchMode = app(WatchModeService::class);
                    $sources = $watchMode->getTitleSources($imdbId, ['IN']);

                    if ($sources !== null) {
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
            ]);
        }

        $recentlyReleasedMovies = Cache::remember('recently-released-movies', now()->endOfDay(), function () {
            return MovieDetail::recentlyReleased()->inRandomOrder()->take(6)->get([
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
            ]);
        });

        $recommendedMovies = Cache::remember('recommended-movies', now()->endOfDay(), function () {
            return MovieDetail::recommended()->inRandomOrder()->take(6)->get([
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
            ]);
        });

        return Inertia::render('Show', [
            'detail' => $detail,
            'sources' => $sources,
            'recentlyReleasedMovies' => $recentlyReleasedMovies,
            'recommendedMovies' => $recommendedMovies,
        ]);
    }

    /**
     * @return array|mixed
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
        defer(fn() => $movie->incrementViews());
        $this->updateDetailInBG($imdbId);

        return $movie;
    }

    private function updateDetailInBG(string $imdbId): void
    {
        defer(function () use ($imdbId) {
            $OMDBApiService = app(OMDBApiService::class);
            $updatedDetail = $OMDBApiService->getById($imdbId);

            MovieDetail::updateOrCreate([
                'imdb_id' => $imdbId,
            ], [
                'title' => $updatedDetail['Title'],
                'year' => $updatedDetail['Year'],
                'release_date' => $updatedDetail['Released'],
                'poster' => $updatedDetail['Poster'],
                'type' => $updatedDetail['Type'],
                'imdb_rating' => $this->isValue($updatedDetail['imdbRating']) ? $updatedDetail['imdbRating'] : 0,
                'imdb_votes' => $this->isValue($updatedDetail['imdbVotes']) ? str_replace(
                    ',',
                    '',
                    $updatedDetail['imdbVotes']
                ) : 0,
                'genre' => $updatedDetail['Genre'],
                'director' => substr($updatedDetail['Director'], 0, 255),
                'writer' => substr($updatedDetail['Writer'], 0, 255),
                'actors' => substr($updatedDetail['Actors'], 0, 255),
                'details' => $updatedDetail,
            ]);
        });
    }

    private function isValue($value)
    {
        return $value && $value != 'N/A';
    }

    /**
     * @return array|mixed
     */
    public function fetchFromAPI(string $imdbId): mixed
    {
        $OMDBApiService = app(OMDBApiService::class);
        $detail = $OMDBApiService->getById($imdbId);

        defer(function () use ($detail, $imdbId) {
            $voted = $detail['imdbVotes'] && $detail['imdbVotes'] != 'N/A'
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
                'imdb_rating' => $detail['imdbRating'] && $detail['imdbRating'] != 'N/A' ? $detail['imdbRating'] : 0,
                'imdb_votes' => $voted,
                'details' => $detail,
            ])->incrementViews();
        });

        return $detail; // Return fetched details
    }
}
