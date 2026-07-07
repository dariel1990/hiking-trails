<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewSubscriptionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function index(): View
    {
        $stats = [
            'active' => Subscription::entitled()->count(),
            'android' => Subscription::entitled()->where('platform', 'android')->count(),
            'web' => Subscription::entitled()->where('platform', 'web')->count(),
            'expiring_soon' => Subscription::entitled()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addDays(7))
                ->count(),
        ];

        $subscriptions = Subscription::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load('user');

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        $subscription->status = 'cancelled';
        $subscription->auto_renewing = false;
        $subscription->save();

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription cancelled.');
    }

    public function extend(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $base = ($subscription->expires_at === null || $subscription->expires_at->isPast())
            ? now()
            : $subscription->expires_at;

        $subscription->expires_at = $base->addDays($validated['days']);

        if (in_array($subscription->status, ['expired', 'cancelled'], true)) {
            $subscription->status = 'active';
        }

        $subscription->save();

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', "Subscription extended by {$validated['days']} day(s).");
    }

    /**
     * Manually send the "new subscription" email to every admin (plus the site
     * owner) for each currently-active subscription. Visiting the route shows a
     * dry-run preview; append ?confirm=send to actually dispatch the emails.
     */
    public function notifyAdmins(Request $request): JsonResponse
    {
        // The site owner receives admin notifications even without an admin account.
        $ownerEmail = 'thomcamus@gmail.com';

        $recipients = User::where('is_admin', true)->pluck('email')
            ->push($ownerEmail)
            ->filter()
            ->unique()
            ->values();

        $subscriptions = Subscription::with('user')->entitled()->get();

        $summary = $subscriptions->map(fn (Subscription $subscription): array => [
            'user' => $subscription->user?->name,
            'email' => $subscription->user?->email,
            'platform' => $subscription->platform,
            'product_id' => $subscription->product_id,
            'expires_at' => $subscription->expires_at?->toDateString(),
        ]);

        if ($request->query('confirm') !== 'send') {
            return response()->json([
                'preview' => true,
                'message' => 'Dry run. Append ?confirm=send to this URL to actually send the emails below.',
                'recipients' => $recipients,
                'active_subscriptions' => $summary,
                'emails_to_send' => $subscriptions->count() * $recipients->count(),
            ]);
        }

        foreach ($subscriptions as $subscription) {
            foreach ($recipients as $email) {
                Notification::route('mail', $email)->notifyNow(new NewSubscriptionNotification($subscription));
            }
        }

        return response()->json([
            'sent' => true,
            'recipients' => $recipients,
            'active_subscriptions' => $summary,
            'emails_sent' => $subscriptions->count() * $recipients->count(),
        ]);
    }
}
