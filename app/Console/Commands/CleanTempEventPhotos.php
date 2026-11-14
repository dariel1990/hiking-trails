<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanTempEventPhotos extends Command
{
    protected $signature = 'events:clean-temp-photos';

    protected $description = 'Clean up temporary event gallery photos that were never attached to an event';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $oneHourAgo = Carbon::now()->subHour()->timestamp;
        $deleted = 0;

        foreach ($disk->allFiles('events/tmp/photos') as $file) {
            if ($disk->lastModified($file) < $oneHourAgo) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Cleaned up {$deleted} temporary event photos.");

        return self::SUCCESS;
    }
}
