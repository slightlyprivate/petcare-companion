<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;

Application::getInstance()->booted(function () {
    $schedule = app(Schedule::class);

    // Schedule cleanup of expired user data exports nightly at 2 AM
    $schedule->command('exports:cleanup')->dailyAt('02:00');

    // Scan for stale pending credit purchases every 15 minutes
    $schedule->command('credits:scan-stale --minutes=30')->everyFifteenMinutes();
});
