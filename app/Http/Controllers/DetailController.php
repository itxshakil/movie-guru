<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use App\Models\ShowPageAnalytics;
use App\Services\BotDetectorService;
use App\Services\OMDBApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DetailController extends Controller
{
    public function show(Request $request, string $imdbId)
    {
        $cacheKey = 'detail.'.$imdbId;
        $cacheTTl = [now()->addHours(18), now()->addHours(24)];
        $detail = Cache::flexible($cacheKey, $cacheTTl, function () use ($imdbId) {
            return $this->fetchDetail($imdbId);
        });

        $botDetector = app(BotDetectorService::class);
        if ($botDetector->isBot($request) === false) {
            defer(function () use ($request, $imdbId) {
                ShowPageAnalytics::create([
                    'imdb_id' => $imdbId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });
        }

        if ($request->wantsJson()) {
            return response()->json(['detail' => $detail]);
        }

        return Inertia::render('Show', [
            'detail' => $detail,
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

    /**
     * @return array|mixed
     */
    public function handleDB(MovieDetail $movie, string $imdbId): mixed
    {
        defer(fn() => $movie->incrementViews());
        $this->updateDetailInBG($imdbId);

        return $movie->details;
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
