<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\MergeGuestLibrary;
use App\Http\Requests\LibraryImportRequest;
use App\Http\Resources\LibraryItemResource;
use App\Models\LibraryItem;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class LibraryImportController extends Controller
{
    public function store(
        LibraryImportRequest $request,
        MergeGuestLibrary $mergeGuestLibrary,
    ): AnonymousResourceCollection {
        /**
         * @var array{items: array<int, array{
         *     imdb_id: string,
         *     title: string,
         *     poster?: string|null,
         *     year?: string|null,
         *     status: string,
         *     reaction?: string|null,
         *     watched_at?: string|null,
         * }>} $validated
         */
        $validated = $request->validated();

        $mergeGuestLibrary->handle($request->user(), $validated['items']);

        return LibraryItemResource::collection(
            LibraryItem::query()->forUser($request->user())->latest()->get(),
        );
    }
}
