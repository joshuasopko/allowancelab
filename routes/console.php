<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run schedule:run every 15 minutes (Railway's most frequent option).
// The between() window catches any firing that lands within 2:00–2:14 AM Central,
// making the schedule tolerant of Railway's ~5-minute execution drift.
// withoutOverlapping() prevents a double-post if two firings land in the window.
Schedule::command('allowance:process')
    ->everyFifteenMinutes()
    ->timezone('America/Chicago')
    ->between('01:00', '01:14')
    ->withoutOverlapping();
