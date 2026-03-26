<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MovieDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class HiddenGemController extends Controller
{
    public function show(): JsonResponse
    {
        $cacheKey = 'hidden_gem_' . now()->format('Y-m-d');

        $gem = Cache::remember($cacheKey, now()->endOfDay(), function () {
            $seed = (int)now()->format('Ymd');

            return MovieDetail::query()
                ->where('imdb_rating', '>=', 7.0)
                ->where('imdb_votes', '<=', 50000)
                ->whereNotNull('poster')
                ->where('poster', '!=', 'N/A')
                ->orderByRaw('RAND(?)', [$seed])
                ->first(['imdb_id', 'title', 'year', 'poster', 'imdb_rating', 'genre', 'director']);
        });

        return response()->json($gem);
    }
}
