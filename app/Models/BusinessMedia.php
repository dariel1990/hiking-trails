<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BusinessMedia extends Model
{
    use HasFactory;

    protected $table = 'business_media';

    protected $fillable = [
        'business_id',
        'media_type',
        'file_path',
        'url',
        'caption',
        'video_provider',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected $appends = ['thumbnail_url', 'embed_url'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->media_type === 'photo' && $this->file_path) {
            return asset('storage/'.$this->file_path);
        }

        return $this->attributes['url'] ?? null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->media_type === 'photo' && $this->file_path) {
            return asset('storage/'.$this->file_path);
        }

        if ($this->media_type === 'video_url') {
            if ($this->video_provider === 'youtube' && isset($this->attributes['url'])) {
                $videoId = $this->extractYoutubeId($this->attributes['url']);
                if ($videoId) {
                    return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
                }
            }

            if ($this->video_provider === 'vimeo' && isset($this->attributes['url'])) {
                $videoId = $this->extractVimeoId($this->attributes['url']);
                if ($videoId) {
                    return "https://vumbnail.com/{$videoId}.jpg";
                }
            }
        }

        return null;
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->media_type !== 'video_url' || ! isset($this->attributes['url'])) {
            return null;
        }

        if ($this->video_provider === 'youtube') {
            $videoId = $this->extractYoutubeId($this->attributes['url']);
            if ($videoId) {
                return "https://www.youtube.com/embed/{$videoId}";
            }
        }

        if ($this->video_provider === 'vimeo') {
            $videoId = $this->extractVimeoId($this->attributes['url']);
            if ($videoId) {
                return "https://player.vimeo.com/video/{$videoId}";
            }
        }

        return null;
    }

    private function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $url, $matches);

        return $matches[1] ?? null;
    }

    private function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);

        return $matches[1] ?? null;
    }

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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BusinessMedia $media) {
            if ($media->is_primary) {
                static::where('business_id', $media->business_id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        static::updating(function (BusinessMedia $media) {
            if ($media->is_primary && $media->isDirty('is_primary')) {
                static::where('business_id', $media->business_id)
                    ->where('id', '!=', $media->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        static::deleting(function (BusinessMedia $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
        });
    }
}
