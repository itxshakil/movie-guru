<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WatchlistStoreRequest;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class WatchlistController extends Controller
{
    public function index(Request $request): Response
    {
        $watchlist = Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['id', 'imdb_id', 'title', 'poster', 'year']);

        return Inertia::render('WatchlistPage', [
            'watchlist' => $watchlist,
        ]);
    }

    public function store(WatchlistStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $item = Watchlist::firstOrCreate(
            ['user_id' => $request->user()->id, 'imdb_id' => $validated['imdb_id']],
            ['title' => $validated['title'], 'poster' => $validated['poster'] ?? null, 'year' => $validated['year'] ?? null],
        );

        return response()->json(['saved' => true, 'id' => $item->id]);
    }

    public function destroy(Request $request, string $imdbId): JsonResponse
    {
        Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->where('imdb_id', $imdbId)
            ->delete();

        return response()->json(['removed' => true]);
    }
}
