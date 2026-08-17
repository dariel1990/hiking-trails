<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReviewPromptEventRequest;
use App\Models\ReviewPromptEvent;
use Illuminate\Http\JsonResponse;

class ReviewPromptEventController extends Controller
{
    /**
     * Record what a visitor did with the review prompt. Called fire-and-forget
     * via navigator.sendBeacon, so the response body is never read — but the
     * status code still matters for debugging.
     */
    public function store(StoreReviewPromptEventRequest $request): JsonResponse
    {
        ReviewPromptEvent::query()->create([
            'user_id' => $request->user()?->id,
            'channel' => $request->string('channel')->toString(),
            'action' => $request->string('action')->toString(),
            'trigger' => $request->input('trigger'),
            'page_url' => $request->input('page_url'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['recorded' => true], 201);
    }
}
