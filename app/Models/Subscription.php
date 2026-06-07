<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory;

    /**
     * Known Google Play subscription SKUs that grant the offline-maps entitlement.
     *
     * @var list<string>
     */
    public const OFFLINE_PRODUCT_IDS = [
        'xs_offline_monthly',
        'xs_offline_annual',
    ];

    /**
     * Stripe (web) SKUs that grant the same XploreSmithers Pro entitlement.
     *
     * @var list<string>
     */
    public const WEB_PRODUCT_IDS = [
        'xs_pro_web_monthly',
        'xs_pro_web_annual',
    ];

    /**
     * Every product that grants Pro, across platforms. A subscription on any of
     * these (Google Play or Stripe) unlocks Pro on both web and the app.
     *
     * @var list<string>
     */
    public const PRO_PRODUCT_IDS = [
        ...self::OFFLINE_PRODUCT_IDS,
        ...self::WEB_PRODUCT_IDS,
    ];

    /**
     * Statuses that count as an active offline entitlement.
     *
     * @var list<string>
     */
    public const ENTITLED_STATUSES = [
        'active',
        'in_grace_period',
    ];

    protected $fillable = [
        'user_id',
        'platform',
        'product_id',
        'purchase_token',
        'status',
        'expires_at',
        'auto_renewing',
        'latest_notification_type',
        'raw_payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'auto_renewing' => 'boolean',
            'latest_notification_type' => 'integer',
            'raw_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to subscriptions that currently grant the offline entitlement.
     */
    public function scopeEntitled(Builder $query): Builder
    {
        return $query->whereIn('status', self::ENTITLED_STATUSES)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isEntitled(): bool
    {
        if (! in_array($this->status, self::ENTITLED_STATUSES, true)) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
