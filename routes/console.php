<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule allowance processing
Schedule::command('allowance:post')->dailyAt('02:00');
Schedule::command('points:reset')->dailyAt('02:01');
