<?php

declare(strict_types=1);

use App\Exceptions\OmdbUnavailableException;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequestLogger;
use App\Services\Core\ErrorReporter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            HandleInertiaRequests::class,
            RequestLogger::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions
            ->respond(function (Response $response, Throwable $exception, Request $request) {
                if (
                    ! app()->environment(['local', 'testing'])
                    && in_array($response->getStatusCode(), [500, 503, 404, 403], strict: true)
                ) {
                    return Inertia::render('Error', ['status' => $response->getStatusCode()])
                        ->toResponse($request)
                        ->setStatusCode($response->getStatusCode());
                }

                if ($response->getStatusCode() === 419) {
                    return back()->with([
                        'error' => 'The page expired, please try again.',
                    ]);
                }

                return $response;
            })
            ->throttle(fn (Throwable $e) => $e instanceof OmdbUnavailableException
                ? Limit::perHour(1)->by('omdb-unavailable')
                : Limit::none())
            ->dontReportDuplicates()
            ->report(function (Throwable $e): void {
                if (app()->environment('production')) {
                    ErrorReporter::report($e);
                }
            });
    })
    ->create();
