<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule permit status update to run every day at midnight (00:00)
Schedule::command('permits:update-statuses')
    ->dailyAt('00:01')
    ->description('Update permit statuses based on visit dates')
    ->withoutOverlapping();

// Backup tasks
Schedule::command('backup:run')
    ->dailyAt('19:00')
    ->description('Run database backup daily after working hours');

Schedule::command('backup:clean')
    ->dailyAt('05:00')
    ->description('Clean up old backups daily');

Schedule::command('backup:monitor')
    ->daily()
    ->description('Monitor backup health daily');