<?php

namespace App\Services;

use App\Models\Facility;

class FacilityService extends BaseService
{
    /**
     * Get all active facilities with their media.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActiveFacilities(): array
    {
        if ($this->isMobile()) {
            return $this->apiGet('/facilities');
        }

        $facilities = Facility::where('is_active', true)
            ->with(['media' => function ($query) {
                $query->orderBy('sort_order')->orderBy('created_at');
            }])
            ->get();

        return $facilities->map(function ($facility) {
            return [
                'id' => $facility->id,
                'name' => $facility->name,
                'facility_type' => $facility->facility_type,
                'facility_type_label' => $facility->facility_type_label,
                'description' => $facility->description,
                'latitude' => $facility->latitude,
                'longitude' => $facility->longitude,
                'icon' => $facility->icon,
                'media_count' => $facility->media_count,
                'media' => $facility->media->map(function ($media) {
                    $photoUrl = $media->file_path ? asset('storage/'.$media->file_path) : ($media->url ?? null);
                    $videoUrl = $media->url ?? null;

                    return [
                        'id' => $media->id,
                        'media_type' => $media->media_type,
                        'url' => $media->media_type === 'photo' ? $photoUrl : $videoUrl,
                        'thumbnail_url' => $media->media_type === 'photo' ? $photoUrl : $media->thumbnail_url,
                        'embed_url' => $media->embed_url,
                        'caption' => $media->caption,
                        'is_primary' => $media->is_primary,
                        'video_provider' => $media->video_provider,
                    ];
                }),
            ];
        })->toArray();
    }
}
