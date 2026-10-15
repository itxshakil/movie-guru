<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TrendingMovieResource;
use App\Services\TrendingMovieService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TrendingMovieController extends Controller
{
    public function index(TrendingMovieService $trendingMovieService): AnonymousResourceCollection
    {
        return TrendingMovieResource::collection($trendingMovieService->fetch());
    }
}
