<?php

namespace App\Notifications;

use App\Models\TrailPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrailPhotoRejectedNotification extends Notification implements ShouldQueue
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
        $trailName = $this->photo->trail?->name ?? 'a trail';

        $message = (new MailMessage)
            ->subject('Update on your XploreSmithers photo submission')
            ->greeting('Hi '.($this->photo->name ?: 'there').',')
            ->line("Thanks for submitting a photo of **{$trailName}** to XploreSmithers.")
            ->line('Unfortunately, our team was not able to approve it this time. This usually happens when a photo does not clearly show the trail, contains identifiable people, or does not meet our quality guidelines.');

        if ($this->photo->caption) {
            $message->line('**Your caption was:** '.$this->photo->caption);
        }

        return $message
            ->line('We would love to see more of your shots — feel free to submit another photo any time.')
            ->line('Happy trails!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'trail_photo_id' => $this->photo->id,
            'trail_id' => $this->photo->trail_id,
        ];
    }
}
