<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
