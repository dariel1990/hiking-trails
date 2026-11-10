<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptLegalDocumentsRequest;
use App\Models\LegalAcceptance;
use App\Services\LegalDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalAcceptanceController extends Controller
{
    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function show(Request $request): View|RedirectResponse
    {
        $pending = $this->legalDocuments->pendingFor($request->user());

        if ($pending->isEmpty()) {
            return redirect()->intended(route('home'));
        }

        return view('legal.accept', ['documents' => $this->legalDocuments->reviewPayload($pending)]);
    }

    public function store(AcceptLegalDocumentsRequest $request): RedirectResponse
    {
        $pending = $this->legalDocuments->pendingFor($request->user());

        if ($pending->isEmpty()) {
            return redirect()->intended(route('home'));
        }

        if (! $this->legalDocuments->matchesVersions($request->validated('versions'), $pending)) {
            return redirect()
                ->route('legal.accept.show')
                ->withErrors(['versions' => 'These documents were just updated. Please review the latest versions.']);
        }

        $this->legalDocuments->recordAcceptance($request->user(), $pending, $request, LegalAcceptance::SOURCE_WEB);

        return redirect()->intended(route('home'));
    }
}
