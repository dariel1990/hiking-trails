<?php

namespace Database\Factories;

use App\Models\EmailLog;
use App\Notifications\NewSubscriptionNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'notification_type' => NewSubscriptionNotification::class,
            'recipient_email' => fake()->unique()->safeEmail(),
            'notifiable_type' => null,
            'notifiable_id' => null,
            'subject' => 'New XploreSmithers Pro subscription',
            'status' => EmailLog::STATUS_SENT,
            'error' => null,
            'payload' => null,
            'resent_at' => null,
        ];
    }

    public function failed(?string $error = 'Connection could not be established with host'): self
    {
        return $this->state(fn (): array => [
            'status' => EmailLog::STATUS_FAILED,
            'error' => $error,
        ]);
    }
}
