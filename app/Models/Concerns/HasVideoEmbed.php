<?php

namespace App\Models\Concerns;

/**
 * Adds provider/embed/thumbnail accessors derived from a `video_url` column.
 *
 * @property string|null $video_url
 * @property-read string|null $video_provider
 * @property-read string|null $video_embed_url
 * @property-read string|null $video_thumbnail_url
 */
trait HasVideoEmbed
{
    public function getVideoProviderAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (str_contains($this->video_url, 'youtube.com') || str_contains($this->video_url, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($this->video_url, 'vimeo.com')) {
            return 'vimeo';
        }

        return 'other';
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (! $this->video_url) {
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

    public function getVideoThumbnailUrlAttribute(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        switch ($this->video_provider) {
            case 'youtube':
                $videoId = $this->extractYouTubeId($this->video_url);

                return $videoId ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg" : null;

            case 'vimeo':
                $videoId = $this->extractVimeoId($this->video_url);

                return $videoId ? "https://vumbnail.com/{$videoId}.jpg" : null;

            default:
                return null;
        }
    }

    protected function extractYouTubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts|live)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches);

        return $matches[1] ?? null;
    }

    protected function extractVimeoId(string $url): ?string
    {
        preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|)(\d+)(?:|\/\?)/', $url, $matches);

        return $matches[1] ?? null;
    }
}
