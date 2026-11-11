<?php

namespace App\Notifications;

use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Services\LegalDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LegalUpdateReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Skip users who accepted between the admin pressing send and the queue running.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return $notifiable instanceof User && $this->pendingDocuments($notifiable)->isNotEmpty();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pending = $this->pendingDocuments($notifiable);
        $titles = $pending->map->title()->join(', ', ' and ');

        $message = (new MailMessage)
            ->subject("We've updated our {$titles}")
            ->greeting('Hi '.($notifiable->first_name ?: $notifiable->name).',')
            ->line("We've made some changes to our {$titles}. Please take a moment to review and accept them.");

        foreach ($pending as $document) {
            if ($document->is_material && $document->change_summary) {
                $message->line("**{$document->title()}:** {$document->change_summary}");
            }
        }

        return $message
            ->action('Review the updated terms', route('legal.accept.show'))
            ->line('You can keep using XploreSmithers as normal in the meantime.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $notifiable->getKey(),
            'versions' => $this->pendingDocuments($notifiable)->map->getKey()->all(),
        ];
    }

    /**
     * @return Collection<int, LegalDocumentVersion>
     */
    private function pendingDocuments(object $notifiable): Collection
    {
        return $notifiable instanceof User
            ? app(LegalDocumentService::class)->pendingFor($notifiable)
            : collect();
    }
}
