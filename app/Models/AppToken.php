<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppToken extends Model
{
    protected $fillable = ['token', 'package_name'];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
