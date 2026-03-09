<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule weekly allowance processing at 2:00 AM Central time daily.
// The Laravel scheduler handles DST automatically via Carbon timezone awareness,
// so spring-forward / fall-back won't cause the 2 AM hour to be skipped or doubled.
Schedule::command('allowance:process')->dailyAt('02:00')->timezone('America/Chicago');
