<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempBusinessPhotos extends Command
{
    protected $signature = 'businesses:clean-temp-photos';

    protected $description = 'Clean up temporary business photo uploads that were never attached to a business';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $tempPath = 'businesses/tmp/photos';

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

        $this->info("Cleaned up {$deleted} temporary business photos.");

        return 0;
    }
}
