<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\HeartBeat;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class HeartBeatTest extends TestCase
{
    public function test_heart_beat_job(): void
    {
        Log::shouldReceive('channel')->with('heartbeat')->andReturnSelf();
        Log::shouldReceive('info');
        Log::shouldReceive('error');

        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('getPdo');

        config(['mail.admin.address' => 'admin@example.com']);

        (new HeartBeat())->handle();

        DB::shouldReceive('connection')->andReturnSelf();
        DB::shouldReceive('getPdo')->andThrow(new Exception('Database connection failed.'));

        (new HeartBeat())->handle();

        $this->assertTrue(true);
    }
}
