<?php

declare(strict_types=1);

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
use Override;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        if ($this->app->isProduction()) {
            Model::handleLazyLoadingViolationUsing(static function ($model, string $relation): void {
                logger()->warning('Attempted to lazy load ' . $model::class . '::' . $relation);
            });
        }

        Event::listen(static function (DatabaseBusy $event): void {
            Notification::route('mail', 'dev@example.com')
                ->notify(new DatabaseApproachingMaxConnections(
                    $event->connectionName,
                    $event->connections,
                ));
        });

        DB::whenQueryingForLongerThan(100, static function (Connection $connection, QueryExecuted $event): void {
            Notification::route('mail', config('mail.admin.address'))
                ->notify(new SlowQueryDetected($connection, $event));
        });

        RateLimiter::for('api', static fn(Request $request) => Limit::perMinute(60)->by(
            $request->user()?->id ?: $request->ip(),
        ));

        RateLimiter::for('search', static function (Request $request) {
            $search = $request->get('s');
            $page = $request->get('page', 1);
            $movieType = $request->get('type');
            $year = $request->get('year');

            $cacheKey = 'search-' . $search . '-' . $page . '-' . $movieType . '-' . $year . '-' . $request->ip();

            return Limit::perMinute(10)->by($cacheKey);
        });

        RateLimiter::for('movie-show', static function (Request $request) {
            $imdbId = $request->route('imdbID');
            $cacheKey = 'movie-show.' . $imdbId . '.' . $request->ip();

            return Limit::perMinute(10)->by($cacheKey);
        });

        Vite::prefetch(concurrency: 3);
    }
}
