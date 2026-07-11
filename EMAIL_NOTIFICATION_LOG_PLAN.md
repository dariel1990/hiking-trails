# Email Notification Log + Resend — Plan & Status

> Working doc for continuing this feature on another device.
> Last updated: 2026-07-11. **Status: feature complete and tested; only cleanup + commit remain.**

## Context

Admin notification emails (new Pro subscription alerts, trail-photo submission alerts, password resets) were fire-and-forget: a failed SMTP send left no record, and — because `QUEUE_CONNECTION=sync` — could even break the Stripe webhook / Android billing verify request mid-flight.

Goal (confirmed with owner):
1. Log **all** outgoing notification emails so failures are visible.
2. A **Resend** button on failed entries that sends immediately.
3. UI: dedicated **Email Logs** admin page, linked from the sidebar (Settings group) with a red failed-count badge.

Key mechanism: Laravel 12 dispatches `NotificationSent` / `NotificationFailed` (exception in `$event->data['exception']`) from `NotificationSender::sendToNotifiable()` before rethrowing. Two auto-discovered listeners in `app/Listeners/` cover every notification with no changes to notification classes.

## Done ✅ (all implemented, tested, Pint-clean)

| Piece | File(s) |
|---|---|
| Migration `email_logs` (ran locally) | `database/migrations/2026_07_10_041359_create_email_logs_table.php` |
| Model + factory | `app/Models/EmailLog.php`, `database/factories/EmailLogFactory.php` |
| Listeners (mail channel only, try/catch + report, synchronous) | `app/Listeners/LogSentNotification.php`, `app/Listeners/LogFailedNotification.php`, shared trait `app/Listeners/Concerns/RecordsEmailLogs.php` |
| Controller (index w/ stats + status filter; resend) | `app/Http/Controllers/Admin/AdminEmailLogController.php` |
| Routes `admin.email-logs.index` / `admin.email-logs.resend` | `routes/web.php` (after subscription routes) |
| Admin page (stats cards, filter tabs, table, resend button) | `resources/views/admin/email-logs/index.blade.php` |
| Sidebar link + red badge (failed & not-resent count) | `resources/views/layouts/admin.blade.php` (Settings group) |
| Hardened notify call sites (mail failure can't break business flows) | `app/Services/StripeSubscriptionService.php`, `app/Http/Controllers/Api/BillingController.php`, `app/Http/Controllers/Api/TrailPhotoController.php`, `app/Http/Controllers/Admin/AdminSubscriptionController.php` |
| Feature tests — **15 passing** | `tests/Feature/Admin/EmailLogTest.php` |

### Design decisions locked in
- Each log row = one send attempt. A resend creates a **new** `sent` row (via the listeners); the original failed row keeps status `failed` (audit trail) and gets `resent_at` stamped.
- After a successful resend: blue "Resent" pill in Status column, action column shows green "Resent X ago" checkmark, **Resend button hidden**, controller rejects double resends.
- `payload` column = `base64_encode(serialize($notification))` → generic resend for any notification type. Resend prefers the original notifiable (`$log->notifiable->notifyNow(...)`), falls back to `Notification::route('mail', ...)` for anonymous rows.
- **Password resets special-cased**: resend mints a fresh token (`Password::broker()->getRepository()->create($user)`) because the stored token is likely expired; graceful error if the user was deleted.
- Listeners are synchronous (no ShouldQueue) so the failed row commits before the exception propagates.

### End-to-end verified (2026-07-10, real SMTP via Titan)
All 3 notification types sent to darielbongabong90@gmail.com and received; log rows correct; resend flow exercised via a simulated failed entry.

## Remaining / Next steps

1. **Clean up test data in the local MySQL DB** (created for the live email test):
   - User `test.subscriber@example.com` (id 21)
   - Subscription id 21 (`purchase_token = 'test_token_notification_check'`) — *inflates active-Pro stats until removed*
   - TrailPhoto id 1 (pending, on "Blue Lakes", fake image paths)
   - `email_logs` rows 1–5 (test sends + simulated failure)
2. **Commit the work** — everything is uncommitted on `main` (alongside unrelated in-progress video/sharing changes; consider separate commits).
3. **Deployment**: run `php artisan migrate`; if `php artisan optimize` / `event:cache` is used in prod, re-run it or the listeners won't be discovered.
4. Optional follow-ups discussed but not requested: notify on cancellations/expirations; fix the pre-existing broken test suite (below).

## Known caveats (pre-existing, NOT from this feature)

- **Full `php artisan test` fails (~84 failures) at HEAD** even without these changes (verified against a clean worktree): API tests get 401 because `VerifyAppKey` middleware on `routes/api.php` expects an app-key header the tests don't send; the first failure triggers a "PDOException: already an active transaction" cascade that poisons the rest of the run. → Verify changes by running test files in isolation, e.g. `php artisan test tests/Feature/Admin/EmailLogTest.php`.
- `.env` uses `QUEUE_CONNECTION=sync` — ShouldQueue notifications send inline. If switched to `database` later, run a queue worker or emails will sit in `jobs`.
- Admin views are light-mode only (no `dark:` classes) — the new page follows that.
- IDE "Undefined type" errors in the new files are a stale Intelephense index (vendor was reinstalled mid-session); reload the window.

## Quick verification on the new device

```bash
composer install && php artisan migrate
php artisan test tests/Feature/Admin/EmailLogTest.php   # 15 tests should pass
# then visit /admin/email-logs as an admin
```
