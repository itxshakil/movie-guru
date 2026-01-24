<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

trait LogCommands
{
    /**
     * @param array<string, mixed> $context
     */
    public function log(string $message, array $context = []): void
    {
        Log::info($message, $context);
        if (method_exists($this, 'info')) {
            $this->info($message);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
        if (method_exists($this, 'error')) {
            $this->error($message);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
        if (method_exists($this, 'warn')) {
            $this->warn($message);
        }
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

        if (method_exists($this, 'info')) {
            $this->info($exception->getMessage());
        }
    }
}
