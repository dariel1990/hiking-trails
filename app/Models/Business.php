<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'business_type',
        'description',
        'tagline',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'facebook_url',
        'instagram_url',
        'opening_hours',
        'price_range',
        'is_seasonal',
        'season_open',
        'icon',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_hours' => 'array',
            'is_seasonal' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = ['media_count'];

    public function media()
    {
        return $this->hasMany(BusinessMedia::class)->orderBy('sort_order')->orderBy('created_at');
    }

    public function photos()
    {
        return $this->media()->where('media_type', 'photo');
    }

    public function videos()
    {
        return $this->media()->where('media_type', 'video_url');
    }

    public function primaryMedia(): ?BusinessMedia
    {
        return $this->media()->where('is_primary', true)->first();
    }

    public function getMediaCountAttribute(): int
    {
        return $this->media()->count();
    }

    public function hasMedia(): bool
    {
        return $this->media()->count() > 0;
    }

    public function getIconAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return $this->getDefaultIcon();
    }

    public function getDefaultIcon(): string
    {
        $icons = [
            'cafe' => '☕',
            'restaurant' => '🍽️',
            'bar' => '🍺',
            'grocery' => '🛒',
            'outdoor_gear' => '🎒',
            'accommodation' => '🏨',
            'gas_station' => '⛽',
            'pharmacy' => '💊',
            'medical' => '🏥',
            'bank' => '🏦',
            'arts_gallery' => '🎨',
            'tour_operator' => '🗺️',
            'retail' => '🛍️',
            'brewery' => '🍻',
            'other' => '📍',
        ];

        return $icons[$this->business_type] ?? '📍';
    }

    public static function getBusinessTypes(): array
    {
        return [
            'cafe' => '☕ Cafe',
            'restaurant' => '🍽️ Restaurant',
            'bar' => '🍺 Bar',
            'grocery' => '🛒 Grocery',
            'outdoor_gear' => '🎒 Outdoor Gear',
            'accommodation' => '🏨 Accommodation',
            'gas_station' => '⛽ Gas Station',
            'pharmacy' => '💊 Pharmacy',
            'medical' => '🏥 Medical',
            'bank' => '🏦 Bank',
            'arts_gallery' => '🎨 Arts & Gallery',
            'tour_operator' => '🗺️ Tour Operator',
            'retail' => '🛍️ Retail',
            'brewery' => '🍻 Brewery',
            'other' => '📍 Other',
        ];
    }

    public static function getPriceRanges(): array
    {
        return [
            'free' => 'Free',
            '$' => '$',
            '$$' => '$$',
            '$$$' => '$$$',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('business_type', $type);
    }

    public function getBusinessTypeLabelAttribute(): string
    {
        return static::getBusinessTypes()[$this->business_type] ?? ucwords(str_replace('_', ' ', $this->business_type));
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            if (empty($business->slug)) {
                $base = Str::slug($business->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.++$i;
                }
                $business->slug = $slug;
            }
        });
    }
}
