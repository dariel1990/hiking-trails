<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalTrailData extends Model
{
    protected $fillable = [
        'trail_id', 'season', 'trail_conditions', 'seasonal_notes',
        'accessibility_changes', 'seasonal_features', 'recommended'
    ];

    protected $casts = [
        'trail_conditions' => 'array',
        'accessibility_changes' => 'array',
        'seasonal_features' => 'array',
        'recommended' => 'boolean',
    ];

    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }
}
