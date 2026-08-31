<?php

namespace App\Observers;

use App\Http\Controllers\TownController;
use App\Models\Town;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Flushes a town's cached landing-page payload whenever anything it renders
 * changes. Watches the Town itself plus every model that can appear on the
 * page (trails, businesses, facilities, tours).
 */
class TownContentObserver
{
    public function saved(Model $model): void
    {
        $this->flush($model);
    }

    public function deleted(Model $model): void
    {
        $this->flush($model);
    }

    private function flush(Model $model): void
    {
        // Any of these changes can add or remove a sitemap entry.
        Cache::forget('sitemap:urls');

        if ($model instanceof Town) {
            Cache::forget(TownController::cacheKey($model));

            return;
        }

        /**
         * A record that moved between towns has to clear both pages, so the
         * original value is flushed alongside the current one.
         */
        $townIds = array_filter([
            $model->getAttribute('town_id'),
            $model->getOriginal('town_id'),
        ]);

        if ($townIds === []) {
            return;
        }

        foreach (Town::whereKey(array_unique($townIds))->get() as $town) {
            Cache::forget(TownController::cacheKey($town));
        }
    }
}
