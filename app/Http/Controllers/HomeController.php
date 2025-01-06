<?php

namespace App\Http\Controllers;

use App\Models\SearchQuery;
use App\Services\TitleCleaner;
use App\Services\TrendingQueryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(TrendingQueryService $trendingQueryService)
    {
        $trendingSearchQueries = $trendingQueryService->fetch();

        return Inertia::render('Welcome', compact('trendingSearchQueries'));
    }

}
