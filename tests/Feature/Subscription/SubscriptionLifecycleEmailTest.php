<?php

namespace Tests\Feature\Subscription;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionCanceledNotification;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpiringSoonNotification;
use App\Notifications\SubscriptionLifecycleAdminNotification;
use App\Notifications\SubscriptionPaymentIssueNotification;
use App\Notifications\SubscriptionPurchasedNotification;
use App\Notifications\TrialConvertedNotification;
use App\Notifications\TrialEndingSoonNotification;
use App\Notifications\TrialStartedNotification;
use App\Services\GooglePlaySubscriptionVerifier;
use App\Services\StripeSubscriptionService;
use App\Support\SubscriptionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use RuntimeException;
use Stripe\Subscription as StripeSubscription;
use Tests\TestCase;

class SubscriptionLifecycleEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // Every lifecycle event is copied to this address.
        Setting::set('owner_notification_email', self::OWNER_EMAIL);
    }

    private const OWNER_EMAIL = 'owner@example.test';

    /**
     * Assert the owner received the admin copy for a given event.
     */
    private function assertOwnerNotifiedOf(SubscriptionEvent $event): void
    {
        Notification::assertSentOnDemand(
            SubscriptionLifecycleAdminNotification::class,
            function (SubscriptionLifecycleAdminNotification $notification, array $channels, object $notifiable) use ($event): bool {
                return $notification->event === $event
                    && ($notifiable->routes['mail'] ?? null) === self::OWNER_EMAIL;
            }
        );
    }

    private function trialingStripeSub(string $id = 'sub_trial_123'): StripeSubscription
    {
        return StripeSubscription::constructFrom([
            'id' => $id,
            'status' => 'trialing',
            'customer' => 'cus_abc',
            'trial_end' => now()->addDays(3)->timestamp,
            'cancel_at_period_end' => false,
            'current_period_end' => now()->addDays(3)->timestamp,
            'items' => ['data' => [[
                'price' => ['id' => 'price_monthly_test'],
                'current_period_end' => now()->addDays(3)->timestamp,
            ]]],
        ]);
    }

    public function test_purchase_email_is_sent_when_an_active_subscription_is_created(): void
    {
        $subscription = Subscription::factory()->active()->create();

        Notification::assertSentTo($subscription->user, SubscriptionPurchasedNotification::class);
    }

    public function test_no_purchase_email_when_a_subscription_is_created_already_expired(): void
    {
        Subscription::factory()->expired()->create();

        Notification::assertNothingSent();
    }

    public function test_payment_issue_email_on_transition_to_grace_period(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $subscription->update(['status' => 'in_grace_period']);

        Notification::assertSentTo($subscription->user, SubscriptionPaymentIssueNotification::class);
    }

    public function test_no_email_when_a_webhook_replay_rewrites_the_same_status(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $subscription->update(['status' => 'active', 'raw_payload' => ['replayed' => true]]);

        Notification::assertNotSentTo($subscription->user, SubscriptionPaymentIssueNotification::class);
        Notification::assertNotSentTo($subscription->user, SubscriptionCanceledNotification::class);
        Notification::assertNotSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_cancellation_email_on_transition_to_canceled(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $subscription->update(['status' => 'canceled']);

        Notification::assertSentTo($subscription->user, SubscriptionCanceledNotification::class);
    }

    public function test_cancellation_email_when_auto_renewal_is_turned_off_on_an_active_subscription(): void
    {
        $subscription = Subscription::factory()->active()->create(['auto_renewing' => true]);

        $subscription->update(['auto_renewing' => false]);

        Notification::assertSentTo($subscription->user, SubscriptionCanceledNotification::class);
    }

    public function test_admin_cancel_sends_exactly_one_cancellation_email(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $subscription = Subscription::factory()->active()->create(['auto_renewing' => true]);

        $this->actingAs($admin)->post(route('admin.subscriptions.cancel', $subscription));

        $this->assertSame('canceled', $subscription->fresh()->status);
        Notification::assertSentToTimes($subscription->user, SubscriptionCanceledNotification::class, 1);
    }

    public function test_no_email_when_auto_renewal_is_turned_back_on(): void
    {
        $subscription = Subscription::factory()->active()->create(['auto_renewing' => false]);

        $subscription->update(['auto_renewing' => true]);

        Notification::assertNotSentTo($subscription->user, SubscriptionCanceledNotification::class);
    }

    public function test_expiry_email_on_transition_to_expired(): void
    {
        $subscription = Subscription::factory()->active()->create();

        $subscription->update(['status' => 'expired']);

        Notification::assertSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_expire_lapsed_command_expires_non_renewing_subscriptions_immediately(): void
    {
        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'auto_renewing' => false,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('subscriptions:expire-lapsed')->assertSuccessful();

        $this->assertSame('expired', $subscription->fresh()->status);
        Notification::assertSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_expire_lapsed_gives_auto_renewing_subscriptions_a_renewal_buffer_when_google_is_unreachable(): void
    {
        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->andThrow(new RuntimeException('Google API down'));
        });

        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'auto_renewing' => true,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('subscriptions:expire-lapsed')->assertSuccessful();

        $this->assertSame('active', $subscription->fresh()->status);
        Notification::assertNotSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_expire_lapsed_expires_auto_renewing_subscriptions_after_the_buffer(): void
    {
        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->andThrow(new RuntimeException('Google API down'));
        });

        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'auto_renewing' => true,
            'expires_at' => now()->subDays(2),
        ]);

        $this->artisan('subscriptions:expire-lapsed')->assertSuccessful();

        $this->assertSame('expired', $subscription->fresh()->status);
        Notification::assertSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_expire_lapsed_rescues_a_lapsed_android_row_that_google_says_renewed(): void
    {
        $newExpiry = now()->addMonth()->startOfSecond();

        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m) use ($newExpiry): void {
            $m->shouldReceive('getSubscription')->once()->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_ACTIVE',
                'lineItems' => [[
                    'expiryTime' => $newExpiry->toIso8601String(),
                    'autoRenewingPlan' => ['autoRenewEnabled' => true],
                ]],
            ]);
        });

        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'auto_renewing' => true,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('subscriptions:expire-lapsed')->assertSuccessful();

        $fresh = $subscription->fresh();
        $this->assertSame('active', $fresh->status);
        $this->assertTrue($fresh->expires_at->equalTo($newExpiry));
        Notification::assertNotSentTo($subscription->user, SubscriptionExpiredNotification::class);
        Notification::assertNotSentTo($subscription->user, SubscriptionCanceledNotification::class);
    }

    public function test_expire_lapsed_expires_a_lapsed_android_row_that_google_confirms_expired(): void
    {
        $this->mock(GooglePlaySubscriptionVerifier::class, function (MockInterface $m): void {
            $m->shouldReceive('getSubscription')->once()->andReturn([
                'subscriptionState' => 'SUBSCRIPTION_STATE_EXPIRED',
                'lineItems' => [[
                    'expiryTime' => now()->subHour()->toIso8601String(),
                    'autoRenewingPlan' => ['autoRenewEnabled' => false],
                ]],
            ]);
        });

        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'auto_renewing' => true,
            'expires_at' => now()->subHour(),
        ]);

        $this->artisan('subscriptions:expire-lapsed')->assertSuccessful();

        $this->assertSame('expired', $subscription->fresh()->status);
        Notification::assertSentTo($subscription->user, SubscriptionExpiredNotification::class);
    }

    public function test_trial_will_end_webhook_records_the_trial_without_emailing_the_reminder(): void
    {
        $user = User::factory()->create();

        app(StripeSubscriptionService::class)->handleTrialWillEnd($this->trialingStripeSub(), $user);

        $subscription = Subscription::firstWhere('purchase_token', 'sub_trial_123');
        $this->assertTrue($subscription->is_trial);
        $this->assertNotNull($subscription->trial_ends_at);

        // The reminder is owned by subscriptions:send-trial-reminders so that
        // Apple and Google (which emit no equivalent webhook) behave the same.
        // Sending here too would double-mail Stripe customers.
        Notification::assertNotSentTo($user, TrialEndingSoonNotification::class);
    }

    public function test_trial_will_end_webhook_retry_does_not_resend_the_trial_started_email(): void
    {
        $user = User::factory()->create();
        $service = app(StripeSubscriptionService::class);

        $service->handleTrialWillEnd($this->trialingStripeSub(), $user);
        $service->handleTrialWillEnd($this->trialingStripeSub(), $user->fresh());

        Notification::assertSentToTimes($user, TrialStartedNotification::class, 1);
    }

    public function test_expiry_reminder_is_sent_for_a_non_renewing_subscription_expiring_soon(): void
    {
        $subscription = Subscription::factory()->active()->create([
            'auto_renewing' => false,
            'expires_at' => now()->addDays(3),
        ]);

        $this->artisan('subscriptions:send-expiry-reminders')->assertSuccessful();

        Notification::assertSentTo($subscription->user, SubscriptionExpiringSoonNotification::class);
        $this->assertNotNull($subscription->fresh()->expiry_reminder_sent_at);
    }

    public function test_expiry_reminder_skips_auto_renewing_subscriptions(): void
    {
        $subscription = Subscription::factory()->active()->create([
            'auto_renewing' => true,
            'expires_at' => now()->addDays(3),
        ]);

        $this->artisan('subscriptions:send-expiry-reminders')->assertSuccessful();

        Notification::assertNotSentTo($subscription->user, SubscriptionExpiringSoonNotification::class);
    }

    public function test_expiry_reminder_is_not_sent_twice_for_the_same_period(): void
    {
        $subscription = Subscription::factory()->active()->create([
            'auto_renewing' => false,
            'expires_at' => now()->addDays(3),
            'expiry_reminder_sent_at' => now()->subDay(),
        ]);

        $this->artisan('subscriptions:send-expiry-reminders')->assertSuccessful();

        Notification::assertNotSentTo($subscription->user, SubscriptionExpiringSoonNotification::class);
    }

    // ── The five lifecycle events: subscriber AND owner ──────────────────────

    public function test_trial_start_emails_the_subscriber_and_the_owner(): void
    {
        $subscription = Subscription::factory()->trialing()->create();

        Notification::assertSentTo($subscription->user, TrialStartedNotification::class);
        // A trial must not be announced as a completed purchase.
        Notification::assertNotSentTo($subscription->user, SubscriptionPurchasedNotification::class);
        $this->assertOwnerNotifiedOf(SubscriptionEvent::TrialStarted);
    }

    public function test_trial_conversion_emails_both_even_though_no_status_changes(): void
    {
        $subscription = Subscription::factory()->trialing()->create();
        Notification::fake();

        // The real signal: is_trial drops, status stays "active" throughout.
        $subscription->update(['is_trial' => false]);

        $this->assertSame('active', $subscription->fresh()->status);
        Notification::assertSentTo($subscription->user, TrialConvertedNotification::class);
        $this->assertOwnerNotifiedOf(SubscriptionEvent::TrialConverted);
    }

    public function test_payment_failure_emails_the_subscriber_and_the_owner(): void
    {
        $subscription = Subscription::factory()->active()->createQuietly();

        $subscription->update(['status' => 'in_grace_period']);

        Notification::assertSentTo($subscription->user, SubscriptionPaymentIssueNotification::class);
        $this->assertOwnerNotifiedOf(SubscriptionEvent::PaymentFailed);
    }

    public function test_cancellation_emails_the_subscriber_and_the_owner(): void
    {
        $subscription = Subscription::factory()->active()->createQuietly();

        $subscription->update(['auto_renewing' => false]);

        Notification::assertSentTo($subscription->user, SubscriptionCanceledNotification::class);
        $this->assertOwnerNotifiedOf(SubscriptionEvent::Canceled);
    }

    public function test_trial_reminder_emails_the_subscriber_and_the_owner(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(2),
        ]);
        Notification::fake();

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        Notification::assertSentTo($subscription->user, TrialEndingSoonNotification::class);
        $this->assertOwnerNotifiedOf(SubscriptionEvent::TrialEndingSoon);
        $this->assertNotNull($subscription->fresh()->trial_reminder_sent_at);
    }

    // ── Guards against double-sending ────────────────────────────────────────

    public function test_trial_reminder_is_not_sent_twice(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(2),
        ]);
        Notification::fake();

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();
        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        Notification::assertSentToTimes($subscription->user, TrialEndingSoonNotification::class, 1);
    }

    public function test_trial_reminder_skips_trials_outside_the_reminder_window(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(30),
        ]);
        Notification::fake();

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        Notification::assertNothingSentTo($subscription->user);
    }

    public function test_trial_reminder_stamp_does_not_trigger_a_further_email(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(2),
        ]);
        Notification::fake();

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        // Writing trial_reminder_sent_at re-enters the observer; it changes
        // neither status nor is_trial, so nothing else may be sent.
        Notification::assertSentToTimes($subscription->user, TrialEndingSoonNotification::class, 1);
        Notification::assertNotSentTo($subscription->user, TrialConvertedNotification::class);
    }

    public function test_trial_reminder_does_not_clash_with_the_expiry_reminder_stamp(): void
    {
        $subscription = Subscription::factory()->trialing()->create([
            'trial_ends_at' => now()->addDays(2),
        ]);

        $this->artisan('subscriptions:send-trial-reminders')->assertSuccessful();

        // The two reminder flows previously shared expiry_reminder_sent_at, so
        // sending one silently suppressed the other.
        $this->assertNull($subscription->fresh()->expiry_reminder_sent_at);
    }

    public function test_a_missing_owner_address_still_emails_the_subscriber(): void
    {
        Setting::set('owner_notification_email', '');

        $subscription = Subscription::factory()->trialing()->create();

        Notification::assertSentTo($subscription->user, TrialStartedNotification::class);
        Notification::assertNotSentTo(
            new AnonymousNotifiable,
            SubscriptionLifecycleAdminNotification::class
        );
    }
}
