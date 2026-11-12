<?php

namespace App\Http\Middleware;

use App\Services\LegalDocumentService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the titles of legal documents the user still has to accept with the views,
 * so the account menu can show a notice. It never redirects.
 */
class SharePendingLegalDocuments
{
    /**
     * @var array<int, string>
     */
    private const EXEMPT_ROUTES = [
        'legal.accept.*',
        'terms',
        'privacy-policy',
        'logout',
        'verification.*',
        'admin.*',
    ];

    public function __construct(private LegalDocumentService $legalDocuments) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldPrompt($request)) {
            $pending = $this->legalDocuments->pendingFor($request->user());

            if ($pending->isNotEmpty()) {
                View::share('pendingLegalTitles', $pending->map->title()->all());
            }
        }

        return $next($request);
    }

    private function shouldPrompt(Request $request): bool
    {
        return $request->user() !== null
            && $request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->ajax()
            && ! $request->routeIs(...self::EXEMPT_ROUTES);
    }
}
