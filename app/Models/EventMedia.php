<?php

namespace App\Models;

use Database\Factories\EventMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A photo or video in an event's gallery. Photos are stored on the public
 * disk with a thumbnail; videos are YouTube or Vimeo links.
 */
class EventMedia extends Model
{
    /** @use HasFactory<EventMediaFactory> */
    use HasFactory;

    public const TYPE_PHOTO = 'photo';

    public const TYPE_VIDEO = 'video_url';

    protected $table = 'event_media';

    protected $fillable = [
        'event_id',
        'media_type',
        'file_path',
        'thumbnail_path',
        'url',
        'video_provider',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (EventMedia $media) {
            $paths = array_filter([$media->file_path, $media->thumbnail_path]);
            if ($paths) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isPhoto(): bool
    {
        return $this->media_type === self::TYPE_PHOTO;
    }

    public function isVideo(): bool
    {
        return $this->media_type === self::TYPE_VIDEO;
    }

    /**
     * Full-size photo URL, or the video's page URL.
     */
    public function getMediaUrlAttribute(): ?string
    {
        return $this->isPhoto() && $this->file_path ? asset('storage/'.$this->file_path) : $this->url;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->isPhoto()) {
            $path = $this->thumbnail_path ?: $this->file_path;

            return $path ? asset('storage/'.$path) : null;
        }

        $videoId = $this->videoId();

        return match (true) {
            $videoId === null => null,
            $this->video_provider === 'youtube' => "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg",
            $this->video_provider === 'vimeo' => "https://vumbnail.com/{$videoId}.jpg",
            default => null,
        };
    }

    public function getEmbedUrlAttribute(): ?string
    {
        $videoId = $this->isVideo() ? $this->videoId() : null;

        return match (true) {
            $videoId === null => null,
            $this->video_provider === 'youtube' => "https://www.youtube-nocookie.com/embed/{$videoId}",
            $this->video_provider === 'vimeo' => "https://player.vimeo.com/video/{$videoId}",
            default => null,
        };
    }

    private function videoId(): ?string
    {
        return $this->url ? self::parseVideo($this->url)['id'] ?? null : null;
    }

    /**
     * Provider and id of a YouTube (watch, youtu.be, shorts, embed) or Vimeo link.
     *
     * @return array{provider: string, id: string}|null
     */
    public static function parseVideo(string $url): ?array
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i', $url, $matches)) {
            return ['provider' => 'youtube', 'id' => $matches[1]];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $matches)) {
            return ['provider' => 'vimeo', 'id' => $matches[1]];
        }

        return null;
    }
}
