<?php

namespace App\Console\Commands;

use App\Models\BusinessMedia;
use App\Models\FacilityMedia;
use App\Models\TrailMedia;
use App\Services\ImageThumbnailService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class BackfillMediaThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-media-thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a small gallery thumbnail for existing facility, business, and trail photos that were uploaded before thumbnails existed';

    public function handle(ImageThumbnailService $thumbnailService): int
    {
        $this->backfill(
            FacilityMedia::query()->where('media_type', 'photo')->whereNull('thumbnail_path'),
            'file_path',
            $thumbnailService,
        );

        $this->backfill(
            BusinessMedia::query()->where('media_type', 'photo')->whereNull('thumbnail_path'),
            'file_path',
            $thumbnailService,
        );

        $this->backfill(
            TrailMedia::query()->where('media_type', 'photo')->whereNull('thumbnail_path'),
            'storage_path',
            $thumbnailService,
        );

        return self::SUCCESS;
    }

    private function backfill(Builder $query, string $pathColumn, ImageThumbnailService $thumbnailService): void
    {
        $this->info("Backfilling thumbnails for {$query->getModel()->getTable()}...");

        $processed = 0;
        $skipped = 0;

        $query->chunkById(50, function ($chunk) use ($pathColumn, $thumbnailService, &$processed, &$skipped) {
            foreach ($chunk as $media) {
                $path = $media->{$pathColumn};

                if (! $path || ! Storage::disk('public')->exists($path)) {
                    $skipped++;

                    continue;
                }

                $directory = dirname($path);
                $media->thumbnail_path = $thumbnailService->thumbnailOnly($path, $directory === '.' ? '' : $directory);
                $media->save();

                $processed++;
            }
        });

        $this->info("  processed: {$processed}, skipped (missing file): {$skipped}");
    }
}
