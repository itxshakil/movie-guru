<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Logs a console command's output to the application log as well as the terminal.
 *
 * @phpstan-require-extends Command
 */
trait LogCommands
{
    /**
     * @param array<string, mixed> $context
     */
    public function log(string $message, array $context = []): void
    {
        Log::info($message, $context);
        $this->info($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
        $this->error($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
        $this->warn($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logException(Throwable $exception, array $context = []): void
    {
        Log::error($exception->getMessage(), array_merge($context, [
            'ex_message' => $exception->getMessage(),
            'ex_file' => $exception->getFile(),
            'ex_line' => $exception->getLine(),
            'ex_code' => $exception->getCode(),
            'trace' => $exception->getTrace(),
        ]));

        $this->error($exception->getMessage());
    }
}
