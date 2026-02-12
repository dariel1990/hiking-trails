<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FacilityMedia extends Model
{
    use HasFactory;

    protected $table = 'facility_media';

    protected $fillable = [
        'facility_id',
        'media_type',
        'file_path',
        'url',
        'caption',
        'video_provider',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the facility that owns this media
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the URL for this media item
     */
    public function getUrlAttribute(): ?string
    {
        if ($this->media_type === 'photo' && $this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return $this->attributes['url'] ?? null;
    }

    /**
     * Get thumbnail URL for this media
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->media_type === 'photo' && $this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        if ($this->media_type === 'video_url') {
            // Return video thumbnail if available
            if ($this->video_provider === 'youtube' && $this->url) {
                $videoId = $this->extractYoutubeId($this->url);
                if ($videoId) {
                    return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
                }
            }

            if ($this->video_provider === 'vimeo' && $this->url) {
                $videoId = $this->extractVimeoId($this->url);
                if ($videoId) {
                    return "https://vumbnail.com/{$videoId}.jpg";
                }
            }
        }

        return null;
    }

    /**
     * Extract YouTube video ID from URL
     */
    private function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);

        return $matches[1] ?? null;
    }

    /**
     * Extract Vimeo video ID from URL
     */
    private function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);

        return $matches[1] ?? null;
    }

    /**
     * Get embed URL for video
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->media_type !== 'video_url' || ! $this->url) {
            return null;
        }

        if ($this->video_provider === 'youtube') {
            $videoId = $this->extractYoutubeId($this->url);
            if ($videoId) {
                return "https://www.youtube.com/embed/{$videoId}";
            }
        }

        if ($this->video_provider === 'vimeo') {
            $videoId = $this->extractVimeoId($this->url);
            if ($videoId) {
                return "https://player.vimeo.com/video/{$videoId}";
            }
        }

        return null;
    }

    /**
     * Get video provider from URL
     */
    public static function detectVideoProvider(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)/', $url)) {
            return 'youtube';
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url)) {
            return 'vimeo';
        }

        return null;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new primary media, unset other primaries
        static::creating(function ($media) {
            if ($media->is_primary) {
                static::where('facility_id', $media->facility_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        // When updating to primary, unset other primaries
        static::updating(function ($media) {
            if ($media->is_primary && $media->isDirty('is_primary')) {
                static::where('facility_id', $media->facility_id)
                    ->where('id', '!=', $media->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        // Delete file when media is deleted
        static::deleting(function ($media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
        });
    }
}
