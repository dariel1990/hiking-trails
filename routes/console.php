<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Add your scraping schedule here
Schedule::command('events:scrape --force')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/scraper.log'));