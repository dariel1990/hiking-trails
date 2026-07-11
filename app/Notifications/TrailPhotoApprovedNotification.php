<?php

namespace App\Notifications;

use App\Models\TrailPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrailPhotoApprovedNotification extends Notification implements ShouldQueue
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
        $trailName = $trail?->name ?? 'the trail';

        $message = (new MailMessage)
            ->subject("Your photo of {$trailName} is now live on XploreSmithers")
            ->greeting('Hi '.($this->photo->name ?: 'there').',')
            ->line("Great news — your photo of **{$trailName}** has been approved and is now visible to everyone on XploreSmithers.");

        if ($this->photo->caption) {
            $message->line('**Your caption:** '.$this->photo->caption);
        }

        if ($trail !== null) {
            $message->action('See it on the trail page', route('trails.show', $trail));
        }

        return $message->line('Thanks for sharing your adventure with the community!');
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
