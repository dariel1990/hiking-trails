<?php

namespace App\Services;

use App\Models\Event;
use Throwable;

class EventImportService
{
    /**
     * Upsert scraped events by source_id.
     *
     * @param  array<int, array<string, mixed>>  $events
     * @return array{new: int, updated: int, errors: int}
     */
    public function import(array $events): array
    {
        $stats = ['new' => 0, 'updated' => 0, 'errors' => 0];

        foreach ($events as $eventData) {
            try {
                $existing = Event::where('source_id', $eventData['source_id'])->first();

                $eventData['is_active'] = true;
                $eventData['scraped_at'] = now();

                if ($existing) {
                    $existing->update($eventData);
                    $stats['updated']++;
                } else {
                    Event::create($eventData);
                    $stats['new']++;
                }
            } catch (Throwable $e) {
                report($e);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Deactivate events whose last day has passed (multi-day events stay active until
     * their end date; events an admin switched back on are left alone).
     *
     * @return int Number of events deactivated
     */
    public function deactivatePastEvents(): int
    {
        return Event::deactivateFinished();
    }
}
