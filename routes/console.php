<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');





Schedule::command('attendance:auto-out')
    ->dailyAt('23:59')
    // ->everyMinute()

    ->timezone('Asia/Kolkata');

Schedule::command('attendance:mark-absent')
    ->dailyAt('23:59')
    // ->everyMinute()
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('indiamart:fetch-leads')
    ->everyThirtyMinutes()
    ->timezone('Asia/Kolkata')
    ->withoutOverlapping()
    ->runInBackground();
