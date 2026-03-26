<?php

declare(strict_types=1);

namespace App\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class HeartBeat implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::connection()->getPdo();
            $message = 'Job Heartbeat: Everything is working fine. Application is healthy. 💪🚀';
            Log::channel('heartbeat')->info($message);
        } catch (Exception $exception) {
            $message = 'Job Heartbeat: Uh-oh! Something went wrong. Application may be experiencing issues. 🚨⚡';
            Log::channel('heartbeat')->error($message);
            Log::channel('heartbeat')->error($exception->getMessage());

            $content =
                'The application encountered an issue. Please investigate as soon as possible. Error: '
                . $exception->getMessage();

            Mail::raw($content, static function ($message): void {
                $subject = 'Application Issue Alert';
                $message->to(config('mail.admin.address'))->subject($subject);
            });
        }
    }
}
