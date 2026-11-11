<?php

namespace Tests\Feature\Admin;

use App\Models\EmailLog;
use App\Models\LegalAcceptance;
use App\Models\LegalDocumentVersion;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LegalUpdateReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class LegalReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
        $this->admin = User::factory()->create(['is_admin' => true, 'created_at' => now()->subYears(2)]);
        LegalAcceptance::query()->delete();
    }

    private function pendingUser(array $attributes = []): User
    {
        return User::factory()->create(['created_at' => now()->subYear(), ...$attributes]);
    }

    private function accept(User $user, LegalDocumentVersion $version): void
    {
        LegalAcceptance::query()->create([
            'user_id' => $user->id,
            'legal_document_version_id' => $version->id,
            'accepted_at' => now(),
            'source' => LegalAcceptance::SOURCE_WEB,
        ]);
    }

    private function remindedDaysAgo(User $user, int $days, string $status = EmailLog::STATUS_SENT): void
    {
        $log = EmailLog::query()->create([
            'notification_type' => LegalUpdateReminderNotification::class,
            'recipient_email' => $user->email,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'subject' => 'Reminder',
            'status' => $status,
        ]);
        $log->forceFill(['created_at' => now()->subDays($days)])->save();
    }

    private function sendReminders(array $payload = []): TestResponse
    {
        return $this->actingAs($this->admin)->post(route('admin.legal.reminders.send', LegalDocumentVersion::TERMS), $payload);
    }

    public function test_reminders_go_to_pending_users_and_skip_everyone_else(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $pending = $this->pendingUser();
        $remindedLongAgo = $this->pendingUser();
        $remindedRecently = $this->pendingUser();
        $failedRecently = $this->pendingUser();
        $suspended = $this->pendingUser(['is_active' => false]);
        $accepted = $this->pendingUser();
        $this->accept($accepted, $version);
        $this->remindedDaysAgo($remindedLongAgo, 10);
        $this->remindedDaysAgo($remindedRecently, 2);
        $this->remindedDaysAgo($failedRecently, 1, EmailLog::STATUS_FAILED);

        $this->sendReminders()
            ->assertRedirect(route('admin.legal.version', [LegalDocumentVersion::TERMS, $version->version, 'tab' => 'pending']))
            ->assertSessionHas('success', 'Reminder emails queued for 4 users.');

        Notification::assertSentTo([$pending, $remindedLongAgo, $failedRecently, $this->admin], LegalUpdateReminderNotification::class);
        Notification::assertNotSentTo([$remindedRecently, $suspended, $accepted], LegalUpdateReminderNotification::class);
    }

    public function test_a_zero_day_cooldown_reminds_everyone_pending(): void
    {
        Setting::set('legal_reminder_cooldown_days', 0);
        LegalDocumentVersion::factory()->material()->create();
        $remindedRecently = $this->pendingUser();
        $this->remindedDaysAgo($remindedRecently, 1);

        $this->sendReminders();

        Notification::assertSentTo($remindedRecently, LegalUpdateReminderNotification::class);
    }

    public function test_one_user_can_be_reminded_even_within_the_cooldown(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $user = $this->pendingUser(['email' => 'hiker@example.com']);
        $other = $this->pendingUser();
        $this->remindedDaysAgo($user, 1);

        $this->sendReminders(['user_id' => $user->id])
            ->assertSessionHas('success', 'Reminder email queued for hiker@example.com.');

        Notification::assertSentTo($user, LegalUpdateReminderNotification::class);
        Notification::assertNotSentTo($other, LegalUpdateReminderNotification::class);
    }

    public function test_a_user_who_already_accepted_is_not_reminded(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->pendingUser();
        $this->accept($user, $version);

        $this->sendReminders(['user_id' => $user->id])->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_nothing_is_sent_when_everyone_was_reminded_recently(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $this->remindedDaysAgo($this->admin, 1);
        $this->remindedDaysAgo($this->pendingUser(), 1);

        $this->sendReminders()->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_an_unknown_user_is_rejected(): void
    {
        LegalDocumentVersion::factory()->material()->create();

        $this->sendReminders(['user_id' => 999999])->assertSessionHasErrors('user_id');

        Notification::assertNothingSent();
    }

    public function test_non_admins_cannot_send_reminders(): void
    {
        LegalDocumentVersion::factory()->material()->create();
        $this->pendingUser();

        $this->actingAs(User::factory()->create())->post(route('admin.legal.reminders.send', LegalDocumentVersion::TERMS));

        Notification::assertNothingSent();
    }

    public function test_the_email_names_the_documents_and_links_to_the_review_page(): void
    {
        LegalDocumentVersion::factory()->material()->create(['change_summary' => 'New liability wording.']);
        $user = $this->pendingUser(['first_name' => 'Jane']);

        $mail = (new LegalUpdateReminderNotification)->toMail($user);
        $rendered = (string) $mail->render();

        $this->assertSame("We've updated our Terms & Conditions", $mail->subject);
        $this->assertSame(route('legal.accept.show'), $mail->actionUrl);
        $this->assertStringContainsString('Hi Jane,', $rendered);
        $this->assertStringContainsString('New liability wording.', $rendered);
    }

    public function test_the_email_is_skipped_if_the_user_accepts_before_it_sends(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->pendingUser();
        $notification = new LegalUpdateReminderNotification;

        $this->assertTrue($notification->shouldSend($user, 'mail'));

        $this->accept($user, $version);

        $this->assertFalse($notification->shouldSend($user, 'mail'));
    }

    public function test_the_pending_tab_shows_the_send_button_and_last_reminded_date(): void
    {
        $version = LegalDocumentVersion::factory()->material()->create();
        $user = $this->pendingUser(['name' => 'Recently Emailed']);
        $this->remindedDaysAgo($user, 3);

        $this->actingAs($this->admin)
            ->get(route('admin.legal.version', [LegalDocumentVersion::TERMS, $version->version, 'tab' => 'pending']))
            ->assertOk()
            ->assertSee('Send reminder to 1')
            ->assertSee('Recently Emailed')
            ->assertSee(now()->subDays(3)->format('M j, Y'))
            ->assertSee('Never');
    }
}
