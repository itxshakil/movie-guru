<?php

namespace App\Providers;

use App\Notifications\DatabaseApproachingMaxConnections;
use App\Notifications\SlowQueryDetected;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\DatabaseBusy;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        if($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                logger()->warning('Attempted to lazy load ' . get_class($model) . '::' . $relation);
            });
        }

        Event::listen(function (DatabaseBusy $event) {
            Notification::route('mail', 'dev@example.com')
                ->notify(new DatabaseApproachingMaxConnections(
                    $event->connectionName,
                    $event->connections
                ));
        });

        DB::whenQueryingForLongerThan(100, function (Connection $connection, QueryExecuted $event) {
            Notification::route('mail', config('mail.admin.address'))
                ->notify(new SlowQueryDetected($connection, $event));
        });

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

        Vite::prefetch(concurrency: 3);
    }
}
