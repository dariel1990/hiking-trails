<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempTrailPhotos extends Command
{
    protected $signature = 'trails:clean-temp-photos';

    protected $description = 'Clean up temporary trail photo uploads that were never attached to a trail';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $tempPath = 'trails/tmp/photos';

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

        $this->info("Cleaned up {$deleted} temporary trail photos.");

        return 0;
    }
}
