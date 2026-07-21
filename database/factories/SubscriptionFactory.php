<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform' => 'android',
            'product_id' => fake()->randomElement(Subscription::OFFLINE_PRODUCT_IDS),
            'purchase_token' => fake()->unique()->sha256(),
            'status' => 'active',
            'is_trial' => false,
            'trial_ends_at' => null,
            'expires_at' => now()->addMonth(),
            'auto_renewing' => true,
            'latest_notification_type' => 4,
            'raw_payload' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (): array => [
            'status' => 'active',
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function inGracePeriod(): self
    {
        // Play extends expiryTime forward during the grace window, and spec
        // §4/§6 treat a row as entitled only while expires_at is future/null.
        return $this->state(fn (): array => [
            'status' => 'in_grace_period',
            'expires_at' => now()->addDays(3),
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (): array => [
            'status' => 'expired',
            'expires_at' => now()->subMonth(),
            'auto_renewing' => false,
        ]);
    }

    /**
     * A subscription currently inside its free trial. Status stays "active" —
     * the trial is carried by is_trial, mirroring how the platforms report it.
     */
    public function trialing(): self
    {
        return $this->state(fn (): array => [
            'status' => 'active',
            'is_trial' => true,
            'trial_ends_at' => now()->addDays(7),
            'expires_at' => now()->addDays(7),
            'auto_renewing' => true,
        ]);
    }

    /**
     * An Apple App Store subscription row.
     */
    public function ios(): self
    {
        return $this->state(fn (): array => [
            'platform' => 'ios',
            'product_id' => fake()->randomElement(Subscription::OFFLINE_PRODUCT_IDS),
            'purchase_token' => (string) fake()->unique()->randomNumber(9, true),
            'original_transaction_id' => (string) fake()->unique()->randomNumber(9, true),
            'latest_notification_type' => null,
        ]);
    }

    /**
     * A Stripe (web) subscription row.
     */
    public function web(): self
    {
        return $this->state(fn (): array => [
            'platform' => 'web',
            'product_id' => fake()->randomElement(Subscription::WEB_PRODUCT_IDS),
            'purchase_token' => 'sub_'.fake()->unique()->lexify('??????????'),
            'latest_notification_type' => null,
        ]);
    }
}
