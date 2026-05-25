<?php

namespace App\Notifications;

use App\Models\TrailPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTrailPhotoSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TrailPhoto $photo) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $trail = $this->photo->trail;
        $reviewUrl = url('/admin/trail-photos?status=pending#photo-'.$this->photo->id);

        return (new MailMessage)
            ->subject('New trail photo awaiting review')
            ->greeting('A new photo has been submitted.')
            ->line('Trail: '.$trail?->name)
            ->line('Submitter: '.($this->photo->name ?: 'Anonymous'))
            ->line('Caption: '.($this->photo->caption ?: '—'))
            ->action('Review submission', $reviewUrl)
            ->line('You are receiving this because you are an administrator.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'photo_id' => $this->photo->id,
            'trail_id' => $this->photo->trail_id,
        ];
    }
}
