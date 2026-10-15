<?php

declare(strict_types=1);

use App\Notifications\SlowQueryDetected;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

test('it renders slow query details in the mail', function (): void {
    $connection = DB::connection();
    $event = new QueryExecuted(
        'update `movie_details` set `views` = ? where `id` = ?',
        [5, 700],
        543.21,
        $connection,
    );

    $html = new SlowQueryDetected($connection, $event)->toMail(notifiable: null)->render();

    expect($html)
        ->toContain($connection->getName())
        ->toContain('0.54 seconds')
        ->toContain('update `movie_details` set `views` = &#039;5&#039; where `id` = &#039;700&#039;')
        ->toContain('CRITICAL')
        ->not->toContain('##')
        ->not->toContain('**');
});

test('it labels fast-but-slow queries differently from critical ones', function (): void {
    $connection = DB::connection();
    $event = new QueryExecuted('select 1', [], 150.0, $connection);

    $html = new SlowQueryDetected($connection, $event)->toMail(notifiable: null)->render();

    expect($html)->toContain('SLOW')->not->toContain('CRITICAL');
});
