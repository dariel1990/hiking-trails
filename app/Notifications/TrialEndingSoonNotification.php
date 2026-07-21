<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Services\RegionalPricingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TrialEndingSoonNotification extends Notification implements ShouldQueue
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
        $isAnnual = Str::endsWith((string) $subscription->product_id, 'annual');

        $pricing = app(RegionalPricingService::class);
        $prices = $pricing->forCountry($pricing->defaultCountry());
        $priceLabel = $prices['symbol'].($isAnnual ? $prices['annual'].' per year' : $prices['monthly'].' per month');

        $message = (new MailMessage)
            ->subject('Your XploreSmithers Pro trial is ending soon')
            ->greeting('Your free trial is almost over.');

        // Prefer the explicit trial end date; expires_at is the renewal date and
        // only coincides with it while the trial is the current period.
        $endsAt = $subscription->trial_ends_at ?? $subscription->expires_at;

        if ($endsAt !== null) {
            $message->line('Your XploreSmithers Pro trial ends on **'.$endsAt->toFormattedDateString().'**.');
        } else {
            $message->line('Your XploreSmithers Pro trial is ending in a few days.');
        }

        return $message
            ->line("After that, your subscription ({$subscription->productLabel()}) continues automatically and your payment method will be charged {$priceLabel}.")
            ->line('No action is needed to keep Pro. If you would rather not continue, cancel before the trial ends and you will not be charged.')
            ->action('Manage your subscription', route('settings.subscription'))
            ->line('Happy exploring!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'user_id' => $this->subscription->user_id,
            'expires_at' => $this->subscription->expires_at?->toIso8601String(),
        ];
    }
}
