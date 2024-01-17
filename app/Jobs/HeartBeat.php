<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HeartBeat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::connection()->getPdo();
            $message = "Job Heartbeat: Everything is working fine. Application is healthy. 💪🚀";
            Log::channel('heartbeat')->info($message);
        } catch (Exception $e) {
            $message = "Job Heartbeat: Uh-oh! Something went wrong. Application may be experiencing issues. 🚨⚡";
            Log::channel('heartbeat')->error($message);
            Log::channel('heartbeat')->error($e->getMessage());

            $content = 'The application encountered an issue. Please investigate as soon as possible. Error: ' . $e->getMessage(
                );

            Mail::raw($content, function ($message) {
                $subject = 'Application Issue Alert';
                $message->to(config('mail.admin.address'))->subject($subject);
            });
        }
    }
}
