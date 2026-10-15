<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WatchlistStoreRequest;
use App\Http\Resources\WatchlistItemResource;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WatchlistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|RedirectResponse
    {
        // The watchlist page itself lives at /watchlist for guests and members alike.
        if (! $request->wantsJson()) {
            return redirect()->route('watchlist');
        }

        $watchlist = Watchlist::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get(['imdb_id', 'title', 'poster', 'year']);

        return WatchlistItemResource::collection($watchlist);
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
