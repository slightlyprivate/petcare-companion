<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;

Application::getInstance()->booted(function () {
    $schedule = app(Schedule::class);

    // Schedule cleanup of expired user data exports nightly at 2 AM
    $schedule->command('exports:cleanup')->dailyAt('02:00');
});
