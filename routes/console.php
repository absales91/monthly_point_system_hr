<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


app()->booted(function () {
    $schedule = app(Schedule::class);

    $schedule->command('attendance:auto-out')
        ->dailyAt('23:59')
        ->timezone('Asia/Kolkata');
        
    $schedule->command('attendance:mark-absent')
        ->dailyAt('23:59')
        ->timezone('Asia/Kolkata')
        ->withoutOverlapping()
        ->onOneServer();

     
});