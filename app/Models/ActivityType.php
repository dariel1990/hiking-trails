<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'color', 'description', 'is_active'];

    public function trails()
    {
        return $this->belongsToMany(Trail::class, 'trail_activities')
                    ->withPivot(['activity_notes', 'activity_specific_data'])
                    ->withTimestamps();
    }
}
