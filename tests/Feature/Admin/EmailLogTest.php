<?php

namespace Tests\Feature\Admin;

use App\Models\EmailLog;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class EmailLogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function standardUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function subscription(): Subscription
    {
        return Subscription::factory()->active()->create();
    }

    private function failedLogWithPayload(User $notifiable, Subscription $subscription): EmailLog
    {
        return EmailLog::factory()->failed()->create([
            'recipient_email' => $notifiable->email,
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->id,
            'payload' => base64_encode(serialize(new NewSubscriptionNotification($subscription))),
        ]);
    }

    public function test_sending_a_notification_creates_a_sent_log_row(): void
    {
        $admin = $this->admin();
        $subscription = $this->subscription();

        $admin->notify(new NewSubscriptionNotification($subscription));

        $this->assertDatabaseHas('email_logs', [
            'notification_type' => NewSubscriptionNotification::class,
            'recipient_email' => $admin->email,
            'notifiable_type' => $admin->getMorphClass(),
            'notifiable_id' => $admin->id,
            'status' => EmailLog::STATUS_SENT,
            'subject' => 'New XploreSmithers Pro subscription',
        ]);

        $this->assertNotNull(EmailLog::first()->payload);
    }

    public function test_failed_notification_event_creates_a_failed_log_row(): void
    {
        $admin = $this->admin();
        $subscription = $this->subscription();

        event(new NotificationFailed(
            $admin,
            new NewSubscriptionNotification($subscription),
            'mail',
            ['exception' => new RuntimeException('SMTP connection refused')],
        ));

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => $admin->email,
            'status' => EmailLog::STATUS_FAILED,
            'error' => 'SMTP connection refused',
        ]);
    }

    public function test_non_mail_channel_events_are_not_logged(): void
    {
        $admin = $this->admin();
        $subscription = $this->subscription();

        event(new NotificationFailed(
            $admin,
            new NewSubscriptionNotification($subscription),
            'database',
            ['exception' => new RuntimeException('irrelevant')],
        ));

        $this->assertDatabaseCount('email_logs', 0);
    }

    public function test_guest_cannot_view_email_logs(): void
    {
        $this->get(route('admin.email-logs.index'))
            ->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_view_email_logs(): void
    {
        $this->actingAs($this->standardUser())
            ->get(route('admin.email-logs.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_email_logs_index(): void
    {
        EmailLog::factory()->create(['recipient_email' => 'sent@example.com']);
        EmailLog::factory()->failed()->create(['recipient_email' => 'failed@example.com']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.email-logs.index'));

        $response->assertOk();
        $response->assertSee('sent@example.com');
        $response->assertSee('failed@example.com');
    }

    public function test_admin_can_filter_email_logs_by_status(): void
    {
        EmailLog::factory()->create(['recipient_email' => 'sent@example.com']);
        EmailLog::factory()->failed()->create(['recipient_email' => 'failed@example.com']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.email-logs.index', ['status' => 'failed']));

        $response->assertOk();
        $response->assertSee('failed@example.com');
        $response->assertDontSee('sent@example.com');
    }

    public function test_admin_can_resend_a_failed_email(): void
    {
        $admin = $this->admin();
        $recipient = $this->standardUser();
        $log = $this->failedLogWithPayload($recipient, $this->subscription());

        $response = $this->actingAs($admin)
            ->post(route('admin.email-logs.resend', $log));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNotNull($log->fresh()->resent_at);

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => $recipient->email,
            'status' => EmailLog::STATUS_SENT,
            'notification_type' => NewSubscriptionNotification::class,
        ]);
    }

    public function test_resend_falls_back_to_anonymous_routing_when_notifiable_is_missing(): void
    {
        $log = EmailLog::factory()->failed()->create([
            'recipient_email' => 'owner@example.com',
            'payload' => base64_encode(serialize(new NewSubscriptionNotification($this->subscription()))),
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => 'owner@example.com',
            'status' => EmailLog::STATUS_SENT,
        ]);
    }

    public function test_already_resent_emails_cannot_be_resent_again(): void
    {
        $recipient = $this->standardUser();
        $log = $this->failedLogWithPayload($recipient, $this->subscription());
        $log->update(['resent_at' => now()->subMinute()]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('email_logs', 1);
    }

    public function test_resend_button_is_hidden_after_a_resend(): void
    {
        $recipient = $this->standardUser();
        $log = $this->failedLogWithPayload($recipient, $this->subscription());
        $log->update(['resent_at' => now()->subMinute()]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.email-logs.index'));

        $response->assertOk();
        $response->assertDontSee(route('admin.email-logs.resend', $log));
    }

    public function test_sent_emails_cannot_be_resent(): void
    {
        $log = EmailLog::factory()->create();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('error');
        $this->assertNull($log->fresh()->resent_at);
        $this->assertDatabaseCount('email_logs', 1);
    }

    public function test_resend_without_payload_fails_gracefully(): void
    {
        $log = EmailLog::factory()->failed()->create(['payload' => null]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('error');
        $this->assertNull($log->fresh()->resent_at);
        $this->assertDatabaseCount('email_logs', 1);
    }

    public function test_resending_a_password_reset_mints_a_fresh_token(): void
    {
        $user = $this->standardUser();

        $log = EmailLog::factory()->failed()->create([
            'notification_type' => ResetPasswordNotification::class,
            'recipient_email' => $user->email,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'payload' => base64_encode(serialize(new ResetPasswordNotification('stale-token'))),
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('success');
        $this->assertNotNull($log->fresh()->resent_at);

        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => $user->email,
            'notification_type' => ResetPasswordNotification::class,
            'status' => EmailLog::STATUS_SENT,
        ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_password_reset_resend_fails_gracefully_when_user_is_deleted(): void
    {
        $user = $this->standardUser();

        $log = EmailLog::factory()->failed()->create([
            'notification_type' => ResetPasswordNotification::class,
            'recipient_email' => $user->email,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
        ]);

        DB::table('users')->where('id', $user->id)->delete();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.email-logs.resend', $log));

        $response->assertSessionHas('error');
        $this->assertNull($log->fresh()->resent_at);
    }
}
