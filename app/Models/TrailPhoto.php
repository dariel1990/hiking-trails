<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TrailPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'trail_id',
        'filename',
        'original_name',
        'path',
        'caption',
        'coordinates',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the trail that owns the photo
     */
    public function trail()
    {
        return $this->belongsTo(Trail::class);
    }

    /**
     * Get the full URL of the photo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Get thumbnail URL (implement if you add image processing)
     */
    public function getThumbnailUrlAttribute(): string
    {
        // For now, return the same URL
        // Later you can implement thumbnail generation
        return $this->url;
    }
}