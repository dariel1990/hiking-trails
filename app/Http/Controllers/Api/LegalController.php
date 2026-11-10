<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptLegalDocumentsRequest;
use App\Models\LegalAcceptance;
use App\Services\LegalDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegalController extends Controller
{
    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function current(): JsonResponse
    {
        return response()->json([
            'documents' => $this->legalDocuments->reviewPayload($this->legalDocuments->currentVersions()),
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'pending' => $this->legalDocuments->reviewPayload($this->legalDocuments->pendingFor($request->user())),
        ]);
    }

    public function accept(AcceptLegalDocumentsRequest $request): JsonResponse
    {
        $pending = $this->legalDocuments->pendingFor($request->user());

        if (! $this->legalDocuments->matchesVersions($request->validated('versions'), $pending)) {
            return response()->json([
                'message' => 'The pending documents have changed.',
                'pending' => $this->legalDocuments->reviewPayload($pending),
            ], Response::HTTP_CONFLICT);
        }

        $this->legalDocuments->recordAcceptance($request->user(), $pending, $request, LegalAcceptance::SOURCE_API);

        return response()->json(['pending' => []]);
    }
}
