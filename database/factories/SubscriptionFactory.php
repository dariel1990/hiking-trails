<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
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
}
