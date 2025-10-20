<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanTempGpxFiles extends Command
{
    protected $signature = 'gpx:clean-temp';
    protected $description = 'Clean up temporary GPX files older than 1 hour';

    public function handle()
    {
        $disk = Storage::disk('public');
        $tempPath = 'gpx/temp';
        
        if (!$disk->exists($tempPath)) {
            $this->info('No temp directory found.');
            return 0;
        }

        $files = $disk->files($tempPath);
        $deleted = 0;
        $oneHourAgo = Carbon::now()->subHour()->timestamp;

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