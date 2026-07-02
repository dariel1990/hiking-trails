<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourStop extends Model
{
    protected $fillable = [
        'tour_id',
        'trail_id',
        'trail_feature_id',
        'stop_order',
        'stop_label',
        'driving_notes',
        'estimated_visit_time',
    ];

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(TrailFeature::class, 'trail_feature_id');
    }
}
