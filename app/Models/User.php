<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
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
        'first_name',
        'last_name',
        'avatar',
        'email',
        'bio',
        'phone',
        'google_id',
        'password',
        'is_admin',
        'is_active',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Full public URL for the user's uploaded profile photo, or null if they
     * haven't set one (callers should fall back to the initials avatar).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
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
     * True when the user currently holds an active XploreSmithers Pro entitlement,
     * from any platform (Google Play or Stripe/web). One subscription unlocks both.
     */
    public function hasActiveProEntitlement(): bool
    {
        return $this->subscriptions()
            ->whereIn('product_id', Subscription::PRO_PRODUCT_IDS)
            ->entitled()
            ->exists();
    }

    /**
     * Alias kept for the Android entitlement contract (the app reads "offline").
     */
    public function hasActiveOfflineEntitlement(): bool
    {
        return $this->hasActiveProEntitlement();
    }

    /**
     * The most relevant Pro subscription for entitlement reporting:
     * the entitled one if present, otherwise the most recently updated row.
     */
    public function currentProSubscription(): ?Subscription
    {
        $pro = $this->subscriptions()
            ->whereIn('product_id', Subscription::PRO_PRODUCT_IDS);

        return (clone $pro)->entitled()->latest('updated_at')->first()
            ?? $pro->latest('updated_at')->first();
    }

    public function currentOfflineSubscription(): ?Subscription
    {
        return $this->currentProSubscription();
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
