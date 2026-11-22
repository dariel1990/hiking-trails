<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;

class ExpireFinishedEvents extends Command
{
    /**
     * @var string
     */
    protected $signature = 'events:expire';

    /**
     * @var string
     */
    protected $description = 'Switch off events whose last day has passed (multi-day events stay on until their end date)';

    public function handle(): int
    {
        $count = Event::deactivateFinished();

        $this->info($count === 1 ? '1 finished event switched off.' : "{$count} finished events switched off.");

        return self::SUCCESS;
    }
}
