<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptLegalDocumentsRequest;
use App\Models\LegalAcceptance;
use App\Services\LegalDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LegalAcceptanceController extends Controller
{
    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function show(Request $request): View|RedirectResponse
    {
        $this->rememberReturnPage($request);

        $pending = $this->legalDocuments->pendingFor($request->user());

        if ($pending->isEmpty()) {
            return redirect()->intended(route('home'));
        }

        return view('legal.accept', [
            'documents' => $this->legalDocuments->reviewPayload($pending),
            'returnUrl' => $request->session()->get('url.intended', route('home')),
        ]);
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

        return redirect()->intended(route('home'))->with('success', 'Thanks, your acceptance has been recorded.');
    }

    /**
     * Send the user back to the page they opened the review from (via the account menu).
     */
    private function rememberReturnPage(Request $request): void
    {
        $referer = (string) $request->headers->get('referer');

        if ($referer === '' || $request->session()->has('url.intended')) {
            return;
        }

        if (Str::startsWith($referer, url('/')) && ! Str::startsWith($referer, route('legal.accept.show'))) {
            redirect()->setIntendedUrl($referer);
        }
    }
}
