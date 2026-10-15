<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordTitleView;
use App\Models\MovieDetail;
use App\Services\BotDetectorService;
use App\Services\ImdbIdExtractor;
use App\Services\MovieCollectionService;
use App\Services\MovieDetailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class DetailController extends Controller
{
    public function __construct(
        private readonly MovieDetailService $movieDetailService,
        private readonly MovieCollectionService $movieCollectionService,
    ) {}

    /**
     * @throws Throwable
     */
    public function show(
        Request $request,
        string $imdbId,
        ImdbIdExtractor $imdbIdExtractor,
        BotDetectorService $botDetectorService,
        RecordTitleView $recordTitleView,
    ): Response|JsonResponse {
        $cleanImdbId = $imdbIdExtractor->extract($imdbId);

        throw_unless($cleanImdbId, NotFoundHttpException::class, 'Invalid IMDb ID. Provided');

        $movie = $this->movieDetailService->find($cleanImdbId, (string) $request->ip());
        $movieDetail = $movie instanceof MovieDetail ? $movie : null;

        if (! $botDetectorService->isBot($request) && ! $this->isBackgroundPrefetch($request)) {
            $recordTitleView->handle($cleanImdbId, $request->ip(), $request->userAgent(), $movieDetail);
        }

        $props = [
            'detail' => $movieDetail instanceof MovieDetail ? $movieDetail->details : $movie,
            'sources' => $movieDetail->sources ?? [],
            'affiliateLink' => $movieDetail?->affiliate_link,
            'trailerId' => Cache::get('watchmode.trailer.' . $cleanImdbId) ?: null,
        ];

        if ($request->wantsJson()) {
            return response()->json($props);
        }

        return Inertia::render('Show', [
            ...$props,
            'recentlyReleasedMovies' => $this->movieCollectionService->recentlyReleased(),
            'recommendedMovies' => $this->movieCollectionService->recommended(),
            'similarMovies' => $this->movieCollectionService->similarTo($movieDetail->genre ?? '', $cleanImdbId),
        ]);
    }

    /**
     * The service worker caches saved titles for offline use with "Purpose: prefetch"; those
     * are not page views and must not trigger analytics or third-party lookups.
     */
    private function isBackgroundPrefetch(Request $request): bool
    {
        return $request->header('Purpose') === 'prefetch' && ! $request->hasHeader('X-Inertia');
    }
}
