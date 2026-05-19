<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntitlementController extends Controller
{
    /**
     * The entitlement state the Android EntitlementManager polls/caches.
     * Always 200 for an authenticated user, even with no subscription.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'offline' => $request->user()->offlineEntitlementPayload(),
        ]);
    }
}
