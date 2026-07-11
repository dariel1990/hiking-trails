<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Throwable;

class AdminEmailLogController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', EmailLog::STATUS_SENT)->count(),
            'failed' => EmailLog::failed()->count(),
            'failed_last_7_days' => EmailLog::failed()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $status = $request->query('status');

        if (! in_array($status, [EmailLog::STATUS_SENT, EmailLog::STATUS_FAILED], true)) {
            $status = null;
        }

        $logs = EmailLog::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.email-logs.index', compact('logs', 'stats', 'status'));
    }

    public function resend(EmailLog $emailLog): RedirectResponse
    {
        if ($emailLog->status !== EmailLog::STATUS_FAILED) {
            return back()->with('error', 'Only failed emails can be resent.');
        }

        if ($emailLog->resent_at !== null) {
            return back()->with('error', 'This email was already resent on '.$emailLog->resent_at->format('M j, Y H:i').'.');
        }

        if ($emailLog->notification_type === ResetPasswordNotification::class) {
            return $this->resendPasswordReset($emailLog);
        }

        if ($emailLog->payload === null) {
            return back()->with('error', 'This log entry has no stored payload to resend.');
        }

        try {
            $notification = unserialize(base64_decode($emailLog->payload));

            if (! $notification instanceof BaseNotification) {
                return back()->with('error', 'The stored email payload could not be restored.');
            }

            $notifiable = $emailLog->notifiable;

            if ($notifiable !== null) {
                $notifiable->notifyNow($notification);
            } else {
                Notification::route('mail', $emailLog->recipient_email)->notifyNow($notification);
            }
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Resend failed: '.$e->getMessage());
        }

        $emailLog->update(['resent_at' => now()]);

        return back()->with('success', "Email resent to {$emailLog->recipient_email}.");
    }

    /**
     * The original reset token is likely expired, so mint a fresh one instead
     * of replaying the stored notification.
     */
    private function resendPasswordReset(EmailLog $emailLog): RedirectResponse
    {
        $user = $emailLog->notifiable;

        if (! $user instanceof User) {
            return back()->with('error', 'The user for this password reset no longer exists.');
        }

        try {
            $token = Password::broker()->getRepository()->create($user);
            $user->notifyNow(new ResetPasswordNotification($token));
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Resend failed: '.$e->getMessage());
        }

        $emailLog->update(['resent_at' => now()]);

        return back()->with('success', "Password reset email resent to {$emailLog->recipient_email} with a fresh link.");
    }
}
