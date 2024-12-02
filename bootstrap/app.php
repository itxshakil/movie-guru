<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            if (! app()->environment(['local', 'testing']) && in_array($response->status(), [500, 503, 404, 403])) {
                return Inertia::render('Error', ['status' => $response->status()])
                    ->toResponse($request)
                    ->setStatusCode($response->status());
            } elseif ($response->status() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
        });
    })->create();