<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ScheduledTaskFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $task,
        public string $summary,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("[XploreSmithers] Scheduled task failed: {$this->task}")
            ->greeting('A scheduled task failed.')
            ->line('**Task:** '.$this->task)
            ->line('**Failed at:** '.now()->toDayDateTimeString())
            ->line('**Details:** '.Str::limit($this->summary, 1000))
            ->action('View email logs', route('admin.email-logs.index'))
            ->line('You are receiving this because your address is set as the developer alert email in the admin settings.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task' => $this->task,
            'summary' => $this->summary,
        ];
    }
}
