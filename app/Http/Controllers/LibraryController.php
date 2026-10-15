<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateLibraryItem;
use App\Http\Requests\LibraryItemRequest;
use App\Http\Resources\LibraryItemResource;
use App\Models\LibraryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class LibraryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|RedirectResponse
    {
        // The page lives at /watchlist for guests and members alike; this is its data source.
        if (! $request->wantsJson()) {
            return to_route('watchlist');
        }

        return LibraryItemResource::collection(
            LibraryItem::query()->forUser($request->user())->latest()->get(),
        );
    }

    public function update(
        LibraryItemRequest $request,
        string $imdbId,
        UpdateLibraryItem $updateLibraryItem,
    ): LibraryItemResource {
        /**
         * @var array{
         *     title: string,
         *     poster?: string|null,
         *     year?: string|null,
         *     status: string,
         *     reaction?: string|null,
         * } $validated
         */
        $validated = $request->validated();

        return new LibraryItemResource($updateLibraryItem->handle($request->user(), $imdbId, $validated));
    }

    public function destroy(Request $request, string $imdbId): Response
    {
        LibraryItem::query()->forUser($request->user())->where('imdb_id', $imdbId)->delete();

        return response()->noContent();
    }
}
