<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Get user's initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }

        return $initials;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * True when the user currently holds an active offline-maps entitlement.
     */
    public function hasActiveOfflineEntitlement(): bool
    {
        return $this->subscriptions()
            ->whereIn('product_id', Subscription::OFFLINE_PRODUCT_IDS)
            ->entitled()
            ->exists();
    }

    /**
     * The most relevant offline subscription for entitlement reporting:
     * the entitled one if present, otherwise the most recently updated row.
     */
    public function currentOfflineSubscription(): ?Subscription
    {
        $offline = $this->subscriptions()
            ->whereIn('product_id', Subscription::OFFLINE_PRODUCT_IDS);

        return (clone $offline)->entitled()->latest('updated_at')->first()
            ?? $offline->latest('updated_at')->first();
    }

    /**
     * Build the `offline` entitlement payload the Android app gates on.
     *
     * @return array{active: bool, productId: ?string, status: string, expiresAt: ?string, inGracePeriod: bool}
     */
    public function offlineEntitlementPayload(): array
    {
        $subscription = $this->currentOfflineSubscription();

        if ($subscription === null) {
            return [
                'active' => false,
                'productId' => null,
                'status' => 'expired',
                'expiresAt' => null,
                'inGracePeriod' => false,
            ];
        }

        return [
            'active' => $subscription->isEntitled(),
            'productId' => $subscription->product_id,
            'status' => $subscription->status,
            'expiresAt' => $subscription->expires_at?->toIso8601ZuluString(),
            'inGracePeriod' => $subscription->status === 'in_grace_period',
        ];
    }
}
