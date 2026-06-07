<?php

namespace Tests\Feature\Api;

use App\Models\Subscription;
use App\Models\User;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class BillingVerifyPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_authentication(): void
    {
        $this->postJson('/api/billing/verify-purchase', [])->assertStatus(401);
    }

    public function test_validates_platform_product_and_token(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'ios',
            'productId' => 'not_a_real_sku',
            'purchaseToken' => '',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['platform', 'productId', 'purchaseToken']);
    }

    public function test_active_purchase_is_verified_and_acknowledged(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $expiry = now()->addMonth()->startOfSecond();

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m) use ($expiry): void {
            $m->shouldReceive('getSubscription')->once()->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_ACTIVE',
                'acknowledgementState' => 'ACKNOWLEDGEMENT_STATE_PENDING',
                'lineItems' => [[
                    'expiryTime' => $expiry->toIso8601String(),
                    'autoRenewingPlan' => ['autoRenewEnabled' => true],
                ]],
            ]);
            $m->shouldReceive('acknowledge')->once()->with('xs_offline_monthly', 'tok_active');
        });

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'android',
            'productId' => 'xs_offline_monthly',
            'purchaseToken' => 'tok_active',
        ])->assertOk()
            ->assertJsonPath('entitlement.active', true)
            ->assertJsonPath('entitlement.status', 'active')
            ->assertJsonPath('entitlement.productId', 'xs_offline_monthly')
            ->assertJsonPath('entitlement.inGracePeriod', false);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'purchase_token' => 'tok_active',
            'product_id' => 'xs_offline_monthly',
            'status' => 'active',
            'auto_renewing' => true,
        ]);
    }

    public function test_grace_period_marks_entitlement_active(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->once()->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD',
                'acknowledgementState' => 'ACKNOWLEDGEMENT_STATE_ACKNOWLEDGED',
                'lineItems' => [[
                    'expiryTime' => now()->addDays(3)->toIso8601String(),
                ]],
            ]);
            $m->shouldNotReceive('acknowledge');
        });

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'android',
            'productId' => 'xs_offline_annual',
            'purchaseToken' => 'tok_grace',
        ])->assertOk()
            ->assertJsonPath('entitlement.active', true)
            ->assertJsonPath('entitlement.status', 'in_grace_period')
            ->assertJsonPath('entitlement.inGracePeriod', true);
    }

    public function test_expired_state_marks_entitlement_inactive(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->once()->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_EXPIRED',
                'lineItems' => [[
                    'expiryTime' => now()->subDay()->toIso8601String(),
                ]],
            ]);
            $m->shouldNotReceive('acknowledge');
        });

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'android',
            'productId' => 'xs_offline_monthly',
            'purchaseToken' => 'tok_expired',
        ])->assertOk()
            ->assertJsonPath('entitlement.active', false)
            ->assertJsonPath('entitlement.status', 'expired');
    }

    public function test_token_bound_to_another_user_returns_409(): void
    {
        $owner = User::factory()->create();
        Subscription::factory()->active()->create([
            'user_id' => $owner->id,
            'purchase_token' => 'tok_shared',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldNotReceive('getSubscription');
        });

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'android',
            'productId' => 'xs_offline_monthly',
            'purchaseToken' => 'tok_shared',
        ])->assertStatus(409);
    }

    public function test_google_api_failure_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->once()->andThrow(new \RuntimeException('boom'));
        });

        $this->postJson('/api/billing/verify-purchase', [
            'platform' => 'android',
            'productId' => 'xs_offline_monthly',
            'purchaseToken' => 'tok_fail',
        ])->assertStatus(422)
            ->assertExactJson(['message' => 'Could not verify purchase']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
