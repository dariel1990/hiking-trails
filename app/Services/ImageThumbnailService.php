<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageThumbnailService
{
    /**
     * Re-encode an image upload (or an existing file on the public disk) to WebP,
     * writing both a full-size version and a small gallery thumbnail.
     *
     * @return array{filename: string, path: string, thumbnail_path: string, file_size: int}
     */
    public function process(
        UploadedFile|string $source,
        string $directory,
        int $fullWidth = 1920,
        int $fullHeight = 1080,
        int $thumbWidth = 400,
        int $thumbHeight = 400,
    ): array {
        $manager = new ImageManager(new Driver);
        $binary = $source instanceof UploadedFile
            ? file_get_contents($source->getRealPath())
            : Storage::disk('public')->get($source);

        $image = $manager->read($binary);
        $image->scaleDown(width: $fullWidth, height: $fullHeight);

        $thumb = $manager->read($binary);
        $thumb->cover($thumbWidth, $thumbHeight);

        $filename = Str::random(40).'.webp';
        $path = $directory.'/'.$filename;
        $thumbnailPath = $directory.'/thumbs/'.$filename;
        $webpData = (string) $image->toWebp(85);

        Storage::disk('public')->put($path, $webpData);
        Storage::disk('public')->put($thumbnailPath, (string) $thumb->toWebp(80));

        return [
            'filename' => $filename,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_size' => strlen($webpData),
        ];
    }

    /**
     * Generate only a thumbnail for an image that already exists on the public
     * disk, without touching or duplicating the full-size original. Used to
     * backfill thumbnails for media uploaded before they were generated.
     */
    public function thumbnailOnly(
        string $existingPath,
        string $directory,
        int $thumbWidth = 400,
        int $thumbHeight = 400,
    ): string {
        $manager = new ImageManager(new Driver);
        $binary = Storage::disk('public')->get($existingPath);

        $thumb = $manager->read($binary);
        $thumb->cover($thumbWidth, $thumbHeight);

        $thumbnailPath = $directory.'/thumbs/'.Str::random(40).'.webp';
        Storage::disk('public')->put($thumbnailPath, (string) $thumb->toWebp(80));

        return $thumbnailPath;
    }
}
