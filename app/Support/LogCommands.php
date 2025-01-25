<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

trait LogCommands
{
    public function log(string $message, array $context = []): void
    {
        Log::info($message, $context);
        $this->info($message);
    }

    public function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
        $this->error($message);
    }

    public function logException(Throwable $exception, array $context = []): void
    {
        Log::error($exception->getMessage(), array_merge($context, [
            'ex_message' => $exception->getMessage(),
            'ex_file' => $exception->getFile(),
            'ex_line' => $exception->getLine(),
            'ex_code' => $exception->getCode(),
            'trace' => $exception->getTrace(),
        ]));

        $this->info($exception->getMessage());
    }
}
