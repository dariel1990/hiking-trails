<?php

namespace App\Models;

use App\Observers\SubscriptionObserver;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SubscriptionObserver::class)]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    /**
     * Mobile store subscription SKUs that grant the offline-maps entitlement.
     * The iOS App Store products use the same identifiers as Google Play, so
     * these cover both mobile platforms.
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

    /**
     * Map of Google Play SubscriptionState → local status column.
     *
     * @var array<string, string>
     */
    public const GOOGLE_STATE_MAP = [
        'SUBSCRIPTION_STATE_ACTIVE' => 'active',
        'SUBSCRIPTION_STATE_IN_GRACE_PERIOD' => 'in_grace_period',
        'SUBSCRIPTION_STATE_ON_HOLD' => 'on_hold',
        'SUBSCRIPTION_STATE_CANCELED' => 'canceled',
        'SUBSCRIPTION_STATE_EXPIRED' => 'expired',
    ];

    /**
     * Map of App Store Server API subscription status → local status column.
     * (1 active, 2 expired, 3 billing retry, 4 billing grace period, 5 revoked.)
     *
     * @var array<int, string>
     */
    public const APPLE_STATE_MAP = [
        1 => 'active',
        2 => 'expired',
        3 => 'on_hold',
        4 => 'in_grace_period',
        5 => 'canceled',
    ];

    protected $fillable = [
        'user_id',
        'platform',
        'product_id',
        'purchase_token',
        'original_transaction_id',
        'status',
        'is_trial',
        'trial_ends_at',
        'trial_reminder_sent_at',
        'expires_at',
        'auto_renewing',
        'expiry_reminder_sent_at',
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
            'is_trial' => 'boolean',
            'trial_ends_at' => 'datetime',
            'trial_reminder_sent_at' => 'datetime',
            'auto_renewing' => 'boolean',
            'expiry_reminder_sent_at' => 'datetime',
            'latest_notification_type' => 'integer',
            'raw_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable plan name for the subscription's product SKU.
     */
    public function productLabel(): string
    {
        return match ($this->product_id) {
            'xs_offline_monthly', 'xs_pro_web_monthly' => 'Pro Monthly',
            'xs_offline_annual', 'xs_pro_web_annual' => 'Pro Annual',
            default => (string) $this->product_id,
        };
    }

    /**
     * Human-readable store name for the subscription's platform. Callers used to
     * inline a two-way android/web ternary, which mislabels iOS.
     */
    public function platformLabel(): string
    {
        return match ($this->platform) {
            'ios' => 'App Store',
            'android' => 'Google Play',
            'web' => 'Web',
            default => (string) $this->platform,
        };
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
