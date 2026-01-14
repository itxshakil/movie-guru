<?php

declare(strict_types=1);

use App\Jobs\HeartBeat;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Schedule::job(new HeartBeat())->everyMinute();
Schedule::command('sitemap:generate')
    ->dailyAt('02:00')->timezone('Asia/Kolkata');

Schedule::command('newsletter:send weekly')
    ->saturdays()->at('18:00');

Schedule::command('newsletter:send monthly')
    ->lastDayOfMonth('18:00');
