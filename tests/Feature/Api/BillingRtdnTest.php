<?php

namespace Tests\Feature\Api;

use App\Models\Subscription;
use App\Services\GooglePlaySubscriptionVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\TestCase;

class BillingRtdnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['services.google_play.rtdn_token' => 'shhh']);
    }

    /**
     * @param  array<string, mixed>  $subscriptionNotification
     * @return array<string, mixed>
     */
    private function envelope(array $subscriptionNotification): array
    {
        $data = base64_encode(json_encode([
            'version' => '1.0',
            'packageName' => 'com.xploresmithers.app',
            'subscriptionNotification' => $subscriptionNotification,
        ]));

        return ['message' => ['data' => $data]];
    }

    public function test_wrong_token_is_forbidden(): void
    {
        $this->postJson('/api/billing/rtdn?token=nope', $this->envelope([
            'notificationType' => 2,
            'purchaseToken' => 'tok_x',
        ]))->assertStatus(403);
    }

    public function test_reachable_without_app_key_even_when_one_is_configured(): void
    {
        // With VerifyAppKey active, an unexempted route would 401 a header-less
        // request. Pub/Sub sends no X-App-Key, so the route must be exempt.
        config(['services.app_api_key' => 'a-real-key']);

        $this->postJson('/api/billing/rtdn?token=shhh', $this->envelope([
            'notificationType' => 13,
            'purchaseToken' => 'tok_unknown',
        ]))->assertNoContent();
    }

    public function test_unknown_purchase_token_is_acknowledged_without_creating_a_row(): void
    {
        $this->postJson('/api/billing/rtdn?token=shhh', $this->envelope([
            'notificationType' => 13,
            'purchaseToken' => 'tok_never_seen',
        ]))->assertNoContent();

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_known_token_is_resynced_from_google_and_records_the_notification_type(): void
    {
        $subscription = Subscription::factory()->create([
            'purchase_token' => 'tok_known',
            'status' => 'active',
            'auto_renewing' => true,
        ]);

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->once()->with('tok_known')->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_CANCELED',
                'lineItems' => [[
                    'expiryTime' => now()->addDays(5)->toIso8601String(),
                    'autoRenewingPlan' => ['autoRenewEnabled' => false],
                ]],
            ]);
        });

        // notificationType 3 = SUBSCRIPTION_CANCELED
        $this->postJson('/api/billing/rtdn?token=shhh', $this->envelope([
            'notificationType' => 3,
            'purchaseToken' => 'tok_known',
        ]))->assertNoContent();

        $subscription->refresh();
        $this->assertSame('canceled', $subscription->status);
        $this->assertFalse($subscription->auto_renewing);
        $this->assertSame(3, $subscription->latest_notification_type);
    }
}
