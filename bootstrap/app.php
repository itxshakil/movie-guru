<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                return Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            } elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'error' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        })->dontReportDuplicates()->report(function (Throwable $e) {
            if (app()->isProduction()) {
                $name = get_class($e);
                $message = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                $trace = $e->getTraceAsString();

                $emails = config('mail.admin.address', '');
                $emails = explode(',', $emails);

                $subject = config('app.name').' - Production Error: '.class_basename(
                        $name
                    ).' on line '.$line.' of '.$file;
                $body = "An exception occurred in the production environment:\n\n".
                    "Exception: $name\n".
                    "Message: $message\n".
                    "File: $file\n".
                    "Line: $line\n\n".
                    "Stack Trace:\n$trace\n";

                foreach ($emails as $email) {
                    $email = trim($email);
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        \Illuminate\Support\Facades\Mail::raw($body, function ($mail) use ($email, $subject) {
                            $mail->to($email)->subject($subject);
                        });
                    } else {
                        \Illuminate\Support\Facades\Log::error('Invalid admin email address configured: '.$email);
                    }
                }
            }
        });
    })->create();
