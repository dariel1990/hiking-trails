<?php

namespace Tests\Feature\Subscription;

use App\Models\Subscription;
use App\Models\User;
use App\Services\StripeSubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Stripe\Subscription as StripeSubscription;
use Tests\TestCase;

class WebSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_web_subscription_grants_pro_entitlement(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->web()->active()->create(['user_id' => $user->id]);

        $this->assertTrue($user->fresh()->hasActiveProEntitlement());
    }

    public function test_web_subscription_unlocks_the_app_entitlement_endpoint(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->web()->active()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/me/entitlements')
            ->assertOk()
            ->assertJsonPath('offline.active', true)
            ->assertJsonPath('offline.productId', fn ($id): bool => in_array($id, Subscription::WEB_PRODUCT_IDS, true));
    }

    public function test_expired_web_subscription_does_not_grant_entitlement(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->web()->expired()->create(['user_id' => $user->id]);

        $this->assertFalse($user->fresh()->hasActiveProEntitlement());
    }

    public function test_pro_page_renders_for_guest_and_member(): void
    {
        $this->get('/pro')->assertOk()->assertSee('XploreSmithers Pro');
        $this->actingAs(User::factory()->create())->get('/pro')->assertOk();
    }

    public function test_checkout_requires_authentication(): void
    {
        $this->post('/pro/checkout', ['plan' => 'monthly'])->assertRedirect('/login');
    }

    public function test_checkout_validates_the_plan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/pro/checkout', ['plan' => 'weekly'])
            ->assertSessionHasErrors('plan');
    }

    public function test_checkout_flashes_when_stripe_is_not_configured(): void
    {
        config()->set('services.stripe.enabled', false);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/pro/checkout', ['plan' => 'monthly'])
            ->assertRedirect(route('pro.show'))
            ->assertSessionHas('error');
    }

    public function test_sync_creates_then_expires_a_subscription_row(): void
    {
        config()->set('services.stripe.prices.annual', 'price_annual_test');
        $user = User::factory()->create();
        $service = app(StripeSubscriptionService::class);

        $sub = StripeSubscription::constructFrom([
            'id' => 'sub_test_123',
            'status' => 'active',
            'customer' => 'cus_abc',
            'cancel_at_period_end' => false,
            'current_period_end' => now()->addMonth()->timestamp,
            'items' => ['data' => [[
                'price' => ['id' => 'price_annual_test'],
                'current_period_end' => now()->addMonth()->timestamp,
            ]]],
        ]);

        $service->syncSubscriptionFromStripe($sub, $user);

        $this->assertDatabaseHas('subscriptions', [
            'purchase_token' => 'sub_test_123',
            'platform' => 'web',
            'product_id' => 'xs_pro_web_annual',
            'status' => 'active',
        ]);
        $this->assertSame('cus_abc', $user->fresh()->stripe_customer_id);
        $this->assertTrue($user->fresh()->hasActiveProEntitlement());

        $sub->status = 'canceled';
        $service->syncSubscriptionFromStripe($sub, $user->fresh());

        $this->assertDatabaseHas('subscriptions', ['purchase_token' => 'sub_test_123', 'status' => 'expired']);
        $this->assertFalse($user->fresh()->hasActiveProEntitlement());
    }

    public function test_stripe_status_maps_to_local_status(): void
    {
        $service = app(StripeSubscriptionService::class);

        $this->assertSame('active', $service->mapStatus('trialing'));
        $this->assertSame('active', $service->mapStatus('active'));
        $this->assertSame('in_grace_period', $service->mapStatus('past_due'));
        $this->assertSame('expired', $service->mapStatus('canceled'));
        $this->assertSame('expired', $service->mapStatus('unpaid'));
    }
}
