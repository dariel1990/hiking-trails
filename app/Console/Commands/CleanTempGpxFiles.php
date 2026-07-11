<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTempGpxFiles extends Command
{
    protected $signature = 'gpx:clean-temp';

    protected $description = 'Clean up temporary GPX files older than the configured age';

    public function handle()
    {
        $disk = Storage::disk('public');
        $tempPath = 'gpx/temp';

        if (! $disk->exists($tempPath)) {
            $this->info('No temp directory found.');

            return 0;
        }

        $files = $disk->files($tempPath);
        $deleted = 0;
        $oneHourAgo = Carbon::now()->subHours((int) setting('temp_gpx_max_age_hours'))->timestamp;

        foreach ($files as $file) {
            $lastModified = $disk->lastModified($file);

            if ($lastModified < $oneHourAgo) {
                $disk->delete($file);
                $deleted++;
            }
        }

        $this->info("Cleaned up {$deleted} temporary GPX files.");

        return 0;
    }
}
