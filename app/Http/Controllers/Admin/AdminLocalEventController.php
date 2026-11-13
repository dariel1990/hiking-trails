<?php

namespace App\Http\Controllers\Admin;

/**
 * Admin CRUD for map-based events (e.g. Christmas in the Valley), shown under
 * "Events" in the admin. Events are tours of Tour::EVENT_TYPE, so this reuses
 * the tour admin with the event section switched on.
 */
class AdminLocalEventController extends AdminTourController
{
    protected function isEventSection(): bool
    {
        return true;
    }
}
