<?php

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
