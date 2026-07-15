<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrailVisit extends Model
{
    protected $fillable = [
        'trail_id',
        'user_id',
        'device_type',
        'platform',
        'browser',
        'ip_address',
    ];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(Trail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
