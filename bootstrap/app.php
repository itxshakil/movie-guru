<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
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
        })->dontReportDuplicates()->report(function (Throwable $e): void {
            if (app()->isProduction()) {
                $name = $e::class;
                $message = $e->getMessage();
                $file = $e->getFile();
                $line = $e->getLine();
                $trace = $e->getTraceAsString();

                $emails = config('mail.admin.address', '');
                $emails = explode(',', $emails);

                $subject = config('app.name') . ' - Production Error: ' . class_basename(
                        $name,
                    ) . ' on line ' . $line . ' of ' . $file;
                $body = "An exception occurred in the production environment:\n\n" .
                    sprintf('Exception: %s%s', $name, PHP_EOL) .
                    sprintf('Message: %s%s', $message, PHP_EOL) .
                    sprintf('File: %s%s', $file, PHP_EOL) .
                    "Line: {$line}\n\n" .
                    "Stack Trace:\n{$trace}\n";

                foreach ($emails as $email) {
                    $email = mb_trim($email);
                    if ($email !== '' && $email !== '0' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Mail::raw($body, function ($mail) use ($email, $subject): void {
                            $mail->to($email)->subject($subject);
                        });
                    } else {
                        Log::error('Invalid admin email address configured: ' . $email);
                    }
                }
            }
        });
    })->create();
