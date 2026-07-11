<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionPaymentIssueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subscription = $this->subscription;

        $message = (new MailMessage)
            ->subject('Action needed: problem with your XploreSmithers Pro payment')
            ->greeting('There was a problem with your latest payment.')
            ->line('We could not process the payment for your XploreSmithers Pro subscription ('.$subscription->productLabel().').');

        if ($subscription->status === 'in_grace_period') {
            $message->line('Your Pro access continues for now, but it will be suspended if the payment is not resolved soon.');
        } else {
            $message->line('Your Pro access is currently on hold until the payment is resolved.');
        }

        if ($subscription->platform === 'android') {
            $message
                ->line('Please open the Google Play Store on your device and update your payment method under Payments & subscriptions.')
                ->action('Open your subscription settings', route('settings.subscription'));
        } else {
            $message
                ->line('Please update your payment method to keep your Pro access.')
                ->action('Update payment method', route('settings.subscription'));
        }

        return $message->line('If you have already fixed this, you can ignore this email.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
            'status' => $this->subscription->status,
        ];
    }
}
