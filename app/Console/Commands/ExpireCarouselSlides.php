<?php

namespace App\Console\Commands;

use App\Models\CarouselSlide;
use Illuminate\Console\Command;

class ExpireCarouselSlides extends Command
{
    /**
     * @var string
     */
    protected $signature = 'carousel:expire';

    /**
     * @var string
     */
    protected $description = 'Turn off carousel slides whose end date has passed';

    public function handle(): int
    {
        $count = CarouselSlide::deactivateExpired();

        $this->info($count === 1 ? '1 expired slide turned off.' : "{$count} expired slides turned off.");

        return self::SUCCESS;
    }
}
