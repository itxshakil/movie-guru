<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\HiddenGemRequest;
use App\Models\MovieDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class HiddenGemController extends Controller
{
    private const array COLUMNS = ['imdb_id', 'title', 'year', 'poster', 'imdb_rating', 'genre', 'director'];

    /**
     * Today's hidden gem, or with ?shuffle=1 (the "another gem" button and shake gesture) a random one.
     */
    public function show(HiddenGemRequest $request): JsonResponse
    {
        if ($request->boolean('shuffle')) {
            $gem = $this->hiddenGems()
                ->when($request->filled('exclude'), static fn (Builder $query) => $query
                    ->where('imdb_id', '!=', $request->string('exclude')->value()))
                ->inRandomOrder()
                ->first(self::COLUMNS);

            return response()->json($gem);
        }

        $cacheKey = 'hidden_gem_' . now()->format('Y-m-d');

        $gem = Cache::remember($cacheKey, now()->endOfDay(), fn () => $this->hiddenGems()
            ->inRandomOrder((string) (int) now()->format('Ymd'))
            ->first(self::COLUMNS));

        return response()->json($gem);
    }

    /**
     * @return Builder<MovieDetail>
     */
    private function hiddenGems(): Builder
    {
        return MovieDetail::query()
            ->where('imdb_rating', '>=', 7.0)
            ->where('imdb_votes', '<=', 50_000)
            ->whereNotNull('poster')
            ->where('poster', '!=', 'N/A');
    }
}
