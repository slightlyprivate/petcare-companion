<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Schedule cleanup of expired user data exports nightly at 2 AM
Schedule::command('exports:cleanup')->dailyAt('02:00');
