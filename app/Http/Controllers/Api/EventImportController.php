<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EventImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventImportController extends Controller
{
    public function __construct(private EventImportService $importer) {}

    /**
     * Receive scraped events pushed from a trusted scraper (e.g. a local
     * machine whose IP is not blocked by the events site's firewall) and
     * upsert them into this environment's database.
     */
    public function store(Request $request): JsonResponse
    {
        $token = (string) config('services.events_import.token', '');

        if ($token === '' || ! hash_equals($token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'events' => ['required', 'array', 'min:1', 'max:500'],
            'events.*.source_id' => ['required', 'string', 'max:255'],
            'events.*.title' => ['required', 'string', 'max:500'],
            'events.*.event_date' => ['required', 'date'],
            'events.*.description' => ['nullable', 'string'],
            'events.*.event_time' => ['nullable', 'date_format:H:i:s'],
            'events.*.end_date' => ['nullable', 'date'],
            'events.*.end_time' => ['nullable', 'date_format:H:i:s'],
            'events.*.location' => ['nullable', 'string', 'max:500'],
            'events.*.venue' => ['nullable', 'string', 'max:500'],
            'events.*.organizer' => ['nullable', 'string', 'max:255'],
            'events.*.category' => ['nullable', 'string', 'max:255'],
            'events.*.image_url' => ['nullable', 'url', 'max:1000'],
            'events.*.external_url' => ['nullable', 'url', 'max:1000'],
        ]);

        $stats = $this->importer->import($validated['events']);
        $stats['deactivated'] = $this->importer->deactivatePastEvents();

        Log::info('Event import received', $stats + ['ip' => $request->ip()]);

        return response()->json($stats);
    }
}
