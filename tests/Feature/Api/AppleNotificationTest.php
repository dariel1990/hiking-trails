<?php

namespace Tests\Feature\Api;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionLifecycleAdminNotification;
use App\Notifications\SubscriptionPaymentIssueNotification;
use App\Services\AppStoreSubscriptionVerifier;
use App\Support\SubscriptionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use Tests\TestCase;

class AppleNotificationTest extends TestCase
{
    use RefreshDatabase;

    private const OWNER = 'owner@example.test';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Setting::set('owner_notification_email', self::OWNER);
        config(['services.app_store.bundle_id' => 'com.xploresmithers.app']);
    }

    /** Compact JWS with an unsigned (test) signature; only the payload is read. */
    private function jws(array $payload): string
    {
        $seg = fn (array $a): string => rtrim(strtr(base64_encode((string) json_encode($a)), '+/', '-_'), '=');

        return $seg(['alg' => 'ES256']).'.'.$seg($payload).'.sig';
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    private function signedPayload(string $notificationType, array $transaction, ?string $subtype = null): string
    {
        return $this->jws([
            'notificationType' => $notificationType,
            'subtype' => $subtype,
            'data' => [
                'bundleId' => 'com.xploresmithers.app',
                'environment' => 'Production',
                'signedTransactionInfo' => $this->jws($transaction),
            ],
        ]);
    }

    private function mockState(string $status, bool $isTrial = false): void
    {
        $this->mock(AppStoreSubscriptionVerifier::class, function (MockInterface $m) use ($status, $isTrial): void {
            $m->makePartial();
            $m->shouldReceive('statusForOriginalTransactionId')->andReturn([
                'status' => $status,
                'expiresAt' => now()->addDays(20),
                'autoRenewing' => $status === 'active',
                'isTrial' => $isTrial,
                'originalTransactionId' => 'otx_1',
                'productId' => 'xs_offline_monthly',
                'raw' => ['appleStatus' => 1],
            ]);
        });
    }

    public function test_missing_payload_is_acknowledged_without_side_effects(): void
    {
        $this->postJson('/api/billing/apple/notifications', [])->assertOk();

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_reachable_without_app_key_even_when_one_is_configured(): void
    {
        config(['services.app_api_key' => 'a-real-key']);
        $this->mockState('active');

        $this->postJson('/api/billing/apple/notifications', [
            'signedPayload' => $this->signedPayload('DID_RENEW', [
                'originalTransactionId' => 'otx_never_seen',
            ]),
        ])->assertOk();
    }

    public function test_notification_for_a_known_subscription_resyncs_and_emails(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->ios()->create([
            'user_id' => $user->id,
            'purchase_token' => 'otx_1',
            'original_transaction_id' => 'otx_1',
            'status' => 'active',
        ]);
        Notification::fake();

        $this->mockState('in_grace_period');

        $this->postJson('/api/billing/apple/notifications', [
            'signedPayload' => $this->signedPayload('DID_FAIL_TO_RENEW', [
                'originalTransactionId' => 'otx_1',
            ], 'GRACE_PERIOD'),
        ])->assertOk();

        $this->assertSame('in_grace_period', Subscription::firstWhere('purchase_token', 'otx_1')->status);
        Notification::assertSentTo($user, SubscriptionPaymentIssueNotification::class);
        Notification::assertSentOnDemand(
            SubscriptionLifecycleAdminNotification::class,
            fn (SubscriptionLifecycleAdminNotification $n, array $ch, object $notifiable): bool => $n->event === SubscriptionEvent::PaymentFailed
                && ($notifiable->routes['mail'] ?? null) === self::OWNER,
        );
    }

    public function test_appaccounttoken_links_a_new_subscription_to_a_user(): void
    {
        $user = User::factory()->create(['app_account_token' => 'aat-123']);
        $this->mockState('active');

        $this->postJson('/api/billing/apple/notifications', [
            'signedPayload' => $this->signedPayload('SUBSCRIBED', [
                'originalTransactionId' => 'otx_1',
                'appAccountToken' => 'aat-123',
            ], 'INITIAL_BUY'),
        ])->assertOk();

        $subscription = Subscription::firstWhere('purchase_token', 'otx_1');
        $this->assertNotNull($subscription);
        $this->assertSame($user->id, $subscription->user_id);
        $this->assertSame('ios', $subscription->platform);
    }

    public function test_unlinkable_notification_creates_no_row(): void
    {
        $this->mockState('active');

        $this->postJson('/api/billing/apple/notifications', [
            'signedPayload' => $this->signedPayload('DID_RENEW', [
                'originalTransactionId' => 'otx_orphan',
            ]),
        ])->assertOk();

        $this->assertDatabaseCount('subscriptions', 0);
        Notification::assertNotSentTo(new AnonymousNotifiable, SubscriptionLifecycleAdminNotification::class);
    }
}
