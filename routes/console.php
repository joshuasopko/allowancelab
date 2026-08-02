<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Railway's Cron Schedule service invokes `schedule:run` every 15 minutes, but with
// boot latency the actual execution time drifts by 1+ minutes from the :00/:15/:30/:45
// mark. everyFifteenMinutes() only matches when the wall-clock minute is EXACTLY
// 0/15/30/45, so a delayed boot (e.g. landing at 01:01 instead of 01:00) causes the
// whole day's window to be silently skipped, since the next cron-matching minute (:15)
// falls outside the between() cutoff. everyMinute() always satisfies the cron check,
// so between('01:00','01:14') alone decides eligibility — any drifted tick that lands
// in the window still fires. withoutOverlapping() prevents a double-post if two ticks
// land in the window.
Schedule::command('allowance:process')
    ->everyMinute()
    ->timezone('America/Chicago')
    ->between('01:00', '01:14')
    ->withoutOverlapping();
