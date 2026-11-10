<?php

namespace App\Http\Middleware;

use App\Services\LegalDocumentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegalAccepted
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
        if ($this->shouldPrompt($request) && $this->legalDocuments->pendingFor($request->user())->isNotEmpty()) {
            return redirect()->guest(route('legal.accept.show'));
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
