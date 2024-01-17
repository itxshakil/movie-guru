<?php

namespace Tests\Unit;

use App\Jobs\HeartBeat;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HeartBeatTest extends TestCase
{
    public function testHeartBeatJob()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Log::spy();

        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once();

        HeartBeat::dispatchSync();

        Log::assertLogged('info', function ($log) {
            return $log['message'] === "Job Heartbeat: Everything is working fine. Application is healthy. 💪🚀";
        });

        Mail::assertNothingSent();

        Log::shouldReceive('error')->times(2);

        DB::shouldReceive('connection')->once()->andThrow(new Exception('Database connection failed.'));

        HeartBeat::dispatchSync();

        Log::assertLogged('error', function ($log) {
            return $log['message'] === "Job Heartbeat: Uh-oh! Something went wrong. Application may be experiencing issues. 🚨⚡";
        });

        Mail::assertSent(function (Mailable $mail) {
            return $mail->hasTo(config('mail.admin.address')) &&
                $mail->subject === 'Application Issue Alert' &&
                strpos($mail->build()->content, 'The application encountered an issue') !== false;
        });
    }
}
