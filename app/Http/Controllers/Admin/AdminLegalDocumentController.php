<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PublishLegalDraftRequest;
use App\Http\Requests\Admin\StoreLegalDraftRequest;
use App\Models\LegalDocumentVersion;
use App\Services\LegalDocumentService;
use App\Support\LineDiff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function version(string $type, int $version): View
    {
        $document = $this->publishedVersions($type)->where('version', $version)->with('publisher')->firstOrFail();

        return view('admin.legal.version', [
            'document' => $document,
            'html' => $this->legalDocuments->render($document->content),
        ]);
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
