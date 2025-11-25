<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\HeartBeat;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class HeartBeatTest extends TestCase
{
    public function test_heart_beat_job(): void
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Log::spy();

        DB::shouldReceive('connection')->once()->andReturnSelf();
        DB::shouldReceive('getPdo')->once();

        dispatch_sync(new HeartBeat());

        Log::assertLogged('info', fn($log): bool => $log['message'] === 'Job Heartbeat: Everything is working fine. Application is healthy. 💪🚀');

        Mail::assertNothingSent();

        Log::shouldReceive('error')->times(2);

        DB::shouldReceive('connection')->once()->andThrow(new Exception('Database connection failed.'));

        dispatch_sync(new HeartBeat());

        Log::assertLogged('error', fn($log): bool => $log['message'] === 'Job Heartbeat: Uh-oh! Something went wrong. Application may be experiencing issues. 🚨⚡');

        Mail::assertSent(fn(Mailable $mail): bool => $mail->hasTo(config('mail.admin.address')) &&
            $mail->subject === 'Application Issue Alert' &&
            str_contains((string)$mail->build()->content, 'The application encountered an issue'));
    }
}
