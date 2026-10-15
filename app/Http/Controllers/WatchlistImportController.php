<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MergeGuestWatchlist;
use App\Http\Requests\WatchlistImportRequest;
use App\Http\Resources\WatchlistItemResource;
use App\Models\Watchlist;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WatchlistImportController extends Controller
{
    public function store(WatchlistImportRequest $request, MergeGuestWatchlist $mergeGuestWatchlist): AnonymousResourceCollection
    {
        /** @var array{items: array<int, array{imdb_id: string, title: string, poster?: string|null, year?: string|null}>} $validated */
        $validated = $request->validated();

        $mergeGuestWatchlist->handle($request->user(), $validated['items']);

        return WatchlistItemResource::collection(
            Watchlist::query()
                ->where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->get(['imdb_id', 'title', 'poster', 'year']),
        );
    }
}
