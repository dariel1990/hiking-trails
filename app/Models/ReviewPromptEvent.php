<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewPromptEvent extends Model
{
    /**
     * Channels the prompt can target.
     */
    public const CHANNELS = ['ios', 'android', 'web'];

    /**
     * What the visitor did with the prompt.
     */
    public const ACTIONS = ['shown', 'review_clicked', 'feedback_clicked', 'dismissed'];

    protected $fillable = [
        'user_id',
        'channel',
        'action',
        'trigger',
        'page_url',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
