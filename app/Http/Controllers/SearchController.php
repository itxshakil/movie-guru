<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Search;
use App\Models\SearchQuery;
use App\OMDB\MovieType;
use App\Services\BotDetectorService;
use App\Services\MovieCollectionService;
use App\Services\OMDBApiService;
use App\Services\SharedLinkResolver;
use App\Services\TitleCleaner;
use App\Services\TrendingQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

final class SearchController extends Controller
{
    public function __construct(
        private readonly MovieCollectionService $movieCollectionService,
    ) {}

    public function index(Request $request, SharedLinkResolver $sharedLinkResolver): Response|JsonResponse|RedirectResponse
    {
        // Web Share Target payloads land here; shared IMDb, Letterboxd, RT or JustWatch links open the title.
        if (! $request->wantsJson()) {
            $sharedLinkUrl = $sharedLinkResolver->resolve([
                $request->string('s')->value(),
                $request->string('link')->value(),
                $request->string('name')->value(),
            ]);

            if ($sharedLinkUrl !== null) {
                return redirect()->to($sharedLinkUrl);
            }
        }

        return $this->respond($request, $request->string('s')->value(), includeMovieRowsInJson: false);
    }

    public function show(Request $request, ?string $search): Response|JsonResponse
    {
        return $this->respond($request, $search, includeMovieRowsInJson: true);
    }

    public function logSearchQuery(
        ?string $search,
        ?int $page,
        ?string $movieType,
        ?int $year,
        Request $request,
    ): SearchQuery {
        return SearchQuery::create([
            'query' => $search ?? 'empty',
            'page' => $page,
            'type' => $movieType,
            'year' => $year,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * @return array{0: int, 1: string|null, 2: int|null, 3: Collection<int, string>, 4: string|null, 5: mixed, 6: array<int, MovieType>, 7: string|null}
     */
    public function getSearchData(?string $search, Request $request): array
    {
        $titleCleaner = resolve(TitleCleaner::class);
        $trendingQueryService = resolve(TrendingQueryService::class);
        $OMDBApiService = resolve(OMDBApiService::class);

        $page = $request->integer('page', 1);
        $movieType = $request->get('type');
        $year = $request->get('year') ? $request->integer('year') : null;

        $trendingQueries = $trendingQueryService->fetch();
        $defaultSearches = $trendingQueries->count()
            ? $trendingQueries->random(
                min(5, $trendingQueries->count()),
            )->all()
            : [];

        $search = $titleCleaner->clean($search);

        if ($search === '' && $defaultSearches !== []) {
            $search = $defaultSearches[array_rand($defaultSearches)];
        }

        $cacheKey = 'searcgh-' . $search . '-' . $page . '-' . $movieType . '-' . $year;
        $movies = Cache::flexible(
            $cacheKey,
            [now()->addHours(16), now()->addHours(24)],
            static fn () => $OMDBApiService->searchByTitle($search, $page, $movieType, $year),
        );

        $botdetector = resolve(BotDetectorService::class);
        if ($botdetector->isBot($request) === false) {
            defer(function () use ($request, $year, $movieType, $page, $search, $movies): void {
                $searchQuery = $this->logSearchQuery($search, $page, $movieType, $year, $request);
                $searchQuery->update([
                    'response_at' => now(),
                    'response' => $movies['Response'] === 'True',
                    'response_result' => $movies,
                ]);

                $totalResults = $movies['totalResults'] ?? 0;

                Search::updateOrCreate([
                    'query' => $search,
                ], [
                    'total_results' => $totalResults,
                ])->incrementViews();
            });
        }

        $movieTypes = MovieType::cases();

        $nextUrl = null;
        if (isset($movies['Response']) && $movies['Response'] === 'True') {
            $totalResults = $movies['totalResults'];
            $currentPage = $page;

            if ($totalResults > ($currentPage * 10)) {
                $nextPage = $currentPage + 1;
                $nextUrl = route('search', [
                    's' => $search,
                    'page' => $nextPage,
                    'type' => $movieType,
                    'year' => $year,
                ]);
            }
        }

        return [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl];
    }

    private function respond(Request $request, ?string $search, bool $includeMovieRowsInJson): Response|JsonResponse
    {
        [$page, $movieType, $year, $trendingQueries, $search, $movies, $movieTypes, $nextUrl] = $this->getSearchData(
            $search,
            $request,
        );

        $results = [
            'searchResults' => $movies,
            'search' => $search,
            'page' => $page,
            'movieType' => $movieType,
            'year' => $year,
            'movieTypes' => $movieTypes,
            'nextUrl' => $nextUrl,
            'trendingQueries' => $trendingQueries->random(min(5, $trendingQueries->count())),
        ];

        if ($request->wantsJson() && ! $includeMovieRowsInJson) {
            return response()->json($results);
        }

        $results['recentlyReleasedMovies'] = $this->movieCollectionService->recentlyReleased();
        $results['recommendedMovies'] = $this->movieCollectionService->recommended();

        if ($request->wantsJson()) {
            return response()->json($results);
        }

        return Inertia::render('Search', $results);
    }
}
