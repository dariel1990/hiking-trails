<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrailNetworkSponsor extends Model
{
    protected $fillable = [
        'trail_network_id',
        'name',
        'tagline',
        'logo',
        'url',
        'welcome_message',
        'banner_text',
        'cta_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function trailNetwork(): BelongsTo
    {
        return $this->belongsTo(TrailNetwork::class);
    }

    public function logoUrl(): string
    {
        if ($this->logo) {
            return asset('storage/'.$this->logo);
        }

        return asset('images/xplore-smithers-logo.png');
    }
}
