<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempFacilityPhotos extends Command
{
    protected $signature = 'facilities:clean-temp-photos';

    protected $description = 'Clean up temporary facility photo uploads that were never attached to a facility';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $tempPath = 'facilities/tmp/photos';

        if (! $disk->exists($tempPath)) {
            $this->info('No temp directory found.');

            return 0;
        }

        $files = $disk->allFiles($tempPath);
        $deleted = 0;
        $oneHourAgo = Carbon::now()->subHour()->timestamp;

        foreach ($files as $file) {
            if ($disk->lastModified($file) < $oneHourAgo) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Cleaned up {$deleted} temporary facility photos.");

        return 0;
    }
}
