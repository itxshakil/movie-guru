<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            $search = $request->get('s');
            $page = $request->get('page', 1);
            $movieType = $request->get('type');
            $year = $request->get('year');

            $cacheKey = 'search-' . $search . '-' . $page . '-' . $movieType . '-' . $year. '-' . $request->ip();
            return Limit::perMinute(10)->by($cacheKey);
        });

        RateLimiter::for('movie-show', function (Request $request) {
            $imdbId = $request->route('imdbID');
            $cacheKey = 'movie-show.' .$imdbId.'.' . $request->ip();
            return Limit::perMinute(10)->by($cacheKey);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
