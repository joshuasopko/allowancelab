<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule weekly allowance processing
// Runs hourly but only processes at 2:00 AM for kids whose allowance_day is today
// This command checks points, posts allowance if points >= 1, then resets points
Schedule::command('allowance:process')->hourly();
