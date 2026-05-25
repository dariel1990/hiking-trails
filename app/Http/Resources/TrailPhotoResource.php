<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\TrailPhoto
 */
class TrailPhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'caption' => $this->caption,
            'submitter_name' => $this->name ?: 'Anonymous',
            'submitted_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
