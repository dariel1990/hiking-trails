<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TrailMedia extends Model
{
    use HasFactory;

    protected $table = 'trail_media';

    protected $fillable = [
        'trail_id',
        'media_type',
        'filename',
        'original_name',
        'storage_path',
        'thumbnail_path',
        'video_url',
        'video_provider',
        'duration',
        'file_size',
        'mime_type',
        'coordinates',
        'caption',
        'description',
        'is_featured',
        'sort_order',
        'uploaded_by',
        'taken_at',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'is_featured' => 'boolean',
        'taken_at' => 'datetime',
    ];

    /**
     * Get the trail that owns this media
     */
    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    /**
     * Get the user who uploaded this media
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the features this media is linked to
     */
    public function features()
    {
        return $this->belongsToMany(TrailFeature::class, 'trail_feature_media')
                    ->withPivot(['is_primary', 'sort_order', 'caption_override'])
                    ->withTimestamps();
    }

    /**
     * Check if this media is a photo
     */
    public function isPhoto(): bool
    {
        return $this->media_type === 'photo';
    }

    /**
     * Check if this media is a video
     */
    public function isVideo(): bool
    {
        return in_array($this->media_type, ['video', 'video_url']);
    }

    /**
     * Check if this is an external video
     */
    public function isExternal(): bool
    {
        return $this->media_type === 'video_url';
    }

    /**
     * Check if this is a local video
     */
    public function isLocalVideo(): bool
    {
        return $this->media_type === 'video';
    }

    /**
     * Get the full URL of the media file
     */
    public function getUrlAttribute(): string
    {
        if ($this->isExternal()) {
            return $this->getEmbedUrl();
        }

        return Storage::url($this->storage_path);
    }

    /**
     * Backwards-compatible method wrapper for getUrl() used in some views
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }

        // Fallback to main image for photos
        if ($this->isPhoto()) {
            return $this->url;
        }

        // Default placeholder for videos without thumbnails
        return asset('images/video-placeholder.png');
    }

    /**
     * Backwards-compatible method wrapper for getThumbnail() used by views
     */
    public function getThumbnail()
    {
        return $this->thumbnail_url;
    }

    /**
     * Get embed URL for external videos
     */
    public function getEmbedUrl(): ?string
    {
        if (!$this->isExternal() || !$this->video_url) {
            return null;
        }

        switch ($this->video_provider) {
            case 'youtube':
                $videoId = $this->extractYouTubeId($this->video_url);
                return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;

            case 'vimeo':
                $videoId = $this->extractVimeoId($this->video_url);
                return $videoId ? "https://player.vimeo.com/video/{$videoId}" : null;

            default:
                return $this->video_url;
        }
    }

    /**
     * Extract YouTube video ID from URL
     */
    private function extractYouTubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Extract Vimeo video ID from URL
     */
    private function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)(?:|\/\?)/', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get formatted duration for videos
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Scope to get only photos
     */
    public function scopePhotos($query)
    {
        return $query->where('media_type', 'photo');
    }

    /**
     * Scope to get only videos (both local and external)
     */
    public function scopeVideos($query)
    {
        return $query->whereIn('media_type', ['video', 'video_url']);
    }

    /**
     * Scope to get only unlinked media (not linked to any feature)
     */
    public function scopeUnlinked($query)
    {
        return $query->doesntHave('features');
    }

    /**
     * Scope to get featured media
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When media is deleted, delete the physical files
        static::deleting(function ($media) {
            if ($media->storage_path && Storage::exists($media->storage_path)) {
                Storage::delete($media->storage_path);
            }

            if ($media->thumbnail_path && Storage::exists($media->thumbnail_path)) {
                Storage::delete($media->thumbnail_path);
            }
        });

        // When media is linked to a feature, update the feature's media count
        static::created(function ($media) {
            $media->features()->each(function ($feature) {
                $feature->updateMediaCount();
            });
        });

        static::deleted(function ($media) {
            $media->features()->each(function ($feature) {
                $feature->updateMediaCount();
            });
        });
    }
}