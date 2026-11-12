<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublishLegalDraftRequest;
use App\Http\Requests\Admin\SendLegalRemindersRequest;
use App\Http\Requests\Admin\StoreLegalDraftRequest;
use App\Models\LegalDocumentVersion;
use App\Models\User;
use App\Notifications\LegalUpdateReminderNotification;
use App\Services\LegalDocumentService;
use App\Support\LineDiff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminLegalDocumentController extends Controller
{
    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function index(): View
    {
        $drafts = LegalDocumentVersion::query()->draft()->get()->keyBy('type');

        $documents = collect(LegalDocumentVersion::TITLES)->map(fn (string $title, string $type): array => [
            'type' => $type,
            'title' => $title,
            'current' => $this->legalDocuments->current($type),
            'draft' => $drafts->get($type),
        ]);

        return view('admin.legal.index', ['documents' => $documents]);
    }

    public function history(string $type): View
    {
        return view('admin.legal.history', [
            'type' => $type,
            'title' => LegalDocumentVersion::TITLES[$type],
            'versions' => LegalDocumentVersion::query()
                ->published()
                ->where('type', $type)
                ->with('publisher')
                ->withCount('acceptances')
                ->orderByDesc('version')
                ->get(),
        ]);
    }

    public function version(Request $request, string $type, int $version): View
    {
        $document = $this->publishedVersions($type)->where('version', $version)->with('publisher')->firstOrFail();
        $isCurrent = $this->legalDocuments->current($type)?->is($document) ?? false;
        $tab = in_array($request->query('tab'), ['accepted', 'pending', 'document'], true) ? $request->query('tab') : 'accepted';
        $search = trim((string) $request->query('search'));

        if ($tab === 'pending' && ! $isCurrent) {
            $tab = 'accepted';
        }

        $pendingQuery = $isCurrent ? $this->legalDocuments->pendingUsersQuery($type) : null;

        return view('admin.legal.version', [
            'document' => $document,
            'html' => $tab === 'document' ? $this->legalDocuments->render($document->content) : null,
            'tab' => $tab,
            'search' => $search,
            'isCurrent' => $isCurrent,
            'acceptedCount' => $document->acceptances()->count(),
            'pendingCount' => $pendingQuery?->count(),
            'remindableCount' => $isCurrent ? $this->legalDocuments->remindableUsersQuery($type)->count() : 0,
            'reminderCooldownDays' => (int) setting('legal_reminder_cooldown_days'),
            'acceptances' => $tab === 'accepted' ? $document->acceptances()
                ->with('user')
                ->when($search !== '', fn (Builder $query): Builder => $query->whereHas('user', fn (Builder $users): Builder => $this->searchUsers($users, $search)))
                ->orderByDesc('accepted_at')
                ->paginate(setting('admin_per_page'))
                ->withQueryString() : null,
            'pendingUsers' => $tab === 'pending' ? $this->legalDocuments->withLastReminder($pendingQuery)
                ->when($search !== '', fn (Builder $query): Builder => $this->searchUsers($query, $search))
                ->orderByDesc('created_at')
                ->paginate(setting('admin_per_page'))
                ->withQueryString() : null,
        ]);
    }

    /**
     * Queue a reminder email to one pending user, or to every pending user who
     * has not been reminded within the cooldown.
     */
    public function sendReminders(SendLegalRemindersRequest $request, string $type): RedirectResponse
    {
        $current = $this->legalDocuments->current($type);
        abort_if($current === null, 404);

        $back = redirect()->route('admin.legal.version', [$type, $current->version, 'tab' => 'pending']);
        $userId = $request->validated('user_id');

        if ($userId !== null) {
            $user = $this->legalDocuments->pendingUsersQuery($type)->whereKey($userId)->first();

            if ($user === null) {
                return $back->with('error', 'That user has nothing left to accept.');
            }

            $user->notify(new LegalUpdateReminderNotification);

            return $back->with('success', "Reminder email queued for {$user->email}.");
        }

        $sent = 0;

        $this->legalDocuments->remindableUsersQuery($type)->chunkById(200, function ($users) use (&$sent): void {
            Notification::send($users, new LegalUpdateReminderNotification);
            $sent += $users->count();
        });

        return $sent === 0
            ? $back->with('error', 'Nobody to remind right now. Everyone pending was already emailed recently.')
            : $back->with('success', 'Reminder emails queued for '.number_format($sent).' '.Str::plural('user', $sent).'.');
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function searchUsers(Builder $query, string $search): Builder
    {
        return $query->where(fn (Builder $users): Builder => $users
            ->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%"));
    }

    public function compare(Request $request, string $type): View
    {
        $versions = $this->publishedVersions($type)->orderByDesc('version')->get();

        $to = $versions->firstWhere('version', $request->integer('to')) ?? $versions->first();
        $from = $versions->firstWhere('version', $request->integer('from'))
            ?? $versions->firstWhere('version', $to->version - 1)
            ?? $to;

        $rows = LineDiff::compare($from->content, $to->content);

        return view('admin.legal.compare', [
            'type' => $type,
            'title' => LegalDocumentVersion::TITLES[$type],
            'versions' => $versions,
            'from' => $from,
            'to' => $to,
            'rows' => $rows,
            'addedCount' => collect($rows)->where('type', LineDiff::ADDED)->count(),
            'removedCount' => collect($rows)->where('type', LineDiff::REMOVED)->count(),
        ]);
    }

    public function draft(string $type): View
    {
        $draft = $this->findDraft($type);

        return view('admin.legal.draft', [
            'type' => $type,
            'title' => LegalDocumentVersion::TITLES[$type],
            'draft' => $draft,
            'content' => old('content', $draft?->content ?? $this->legalDocuments->current($type)?->content ?? ''),
        ]);
    }

    public function saveDraft(StoreLegalDraftRequest $request, string $type): RedirectResponse
    {
        $draft = $this->findDraft($type);

        if ($draft !== null) {
            $draft->update(['content' => $request->validated('content')]);
        } else {
            LegalDocumentVersion::query()->create([
                'type' => $type,
                'content' => $request->validated('content'),
                'created_by' => $request->user()->getKey(),
            ]);
        }

        return redirect()
            ->route('admin.legal.draft', $type)
            ->with('success', 'Draft saved. It is not visible to the public until published.');
    }

    public function discardDraft(string $type): RedirectResponse
    {
        $this->findDraft($type)?->delete();

        return redirect()
            ->route('admin.legal.index')
            ->with('success', 'Draft discarded.');
    }

    public function publish(PublishLegalDraftRequest $request, string $type): RedirectResponse
    {
        $draft = $this->findDraft($type);

        if ($draft === null) {
            return redirect()
                ->route('admin.legal.draft', $type)
                ->withErrors(['draft' => 'There is no draft to publish.']);
        }

        if ($draft->content === $this->legalDocuments->current($type)?->content) {
            return redirect()
                ->route('admin.legal.draft', $type)
                ->withErrors(['draft' => 'This draft is identical to the current version, so there is nothing to publish.']);
        }

        $published = $this->legalDocuments->publish(
            $draft,
            $request->user(),
            $request->validated('change_summary'),
            $request->boolean('is_material'),
        );

        return redirect()
            ->route('admin.legal.history', $type)
            ->with('success', "{$published->title()} version {$published->version} is now live.");
    }

    public function preview(StoreLegalDraftRequest $request): JsonResponse
    {
        return response()->json(['html' => $this->legalDocuments->render($request->validated('content'))]);
    }

    private function findDraft(string $type): ?LegalDocumentVersion
    {
        return LegalDocumentVersion::query()->draft()->where('type', $type)->first();
    }

    /**
     * @return Builder<LegalDocumentVersion>
     */
    private function publishedVersions(string $type): Builder
    {
        return LegalDocumentVersion::query()->published()->where('type', $type);
    }
}
