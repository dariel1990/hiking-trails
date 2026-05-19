<?php

namespace Tests\Feature\Api;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EntitlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_entitlements_requires_authentication(): void
    {
        $this->getJson('/api/me/entitlements')->assertStatus(401);
    }

    public function test_user_without_subscription_is_not_entitled(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/me/entitlements')
            ->assertOk()
            ->assertExactJson([
                'offline' => [
                    'active' => false,
                    'productId' => null,
                    'status' => 'expired',
                    'expiresAt' => null,
                    'inGracePeriod' => false,
                ],
            ]);
    }

    public function test_active_subscription_grants_entitlement(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->active()->create([
            'user_id' => $user->id,
            'product_id' => 'xs_offline_annual',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/entitlements')
            ->assertOk()
            ->assertJsonPath('offline.active', true)
            ->assertJsonPath('offline.status', 'active')
            ->assertJsonPath('offline.productId', 'xs_offline_annual')
            ->assertJsonPath('offline.inGracePeriod', false);
    }

    public function test_grace_period_subscription_grants_entitlement(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->inGracePeriod()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/entitlements')
            ->assertOk()
            ->assertJsonPath('offline.active', true)
            ->assertJsonPath('offline.status', 'in_grace_period')
            ->assertJsonPath('offline.inGracePeriod', true);
    }

    public function test_expired_subscription_does_not_grant_entitlement(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->expired()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me/entitlements')
            ->assertOk()
            ->assertJsonPath('offline.active', false)
            ->assertJsonPath('offline.status', 'expired');
    }

    public function test_helper_reflects_active_entitlement(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->hasActiveOfflineEntitlement());

        Subscription::factory()->active()->create(['user_id' => $user->id]);
        $this->assertTrue($user->fresh()->hasActiveOfflineEntitlement());
    }
}
