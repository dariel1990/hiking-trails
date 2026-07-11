<?php

use App\Support\DeveloperAlert;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Add your scraping schedule here
Schedule::command('events:scrape --force')
    ->dailyAt(setting('events_scrape_time', '02:00'))
    ->appendOutputTo(storage_path('logs/scraper.log'))
    ->onFailure(fn () => DeveloperAlert::send('events:scrape', 'Command exited with a non-zero status. See storage/logs/scraper.log for output.'));
