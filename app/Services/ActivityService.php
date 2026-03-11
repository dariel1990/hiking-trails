<?php

namespace App\Services;

use App\Models\ActivityType;
use Illuminate\Support\Collection;

class ActivityService extends BaseService
{
    /**
     * Get all active activity types.
     */
    public function getActiveActivities(): Collection
    {
        if ($this->isMobile()) {
            return $this->toObjectCollection($this->apiGet('/activities'));
        }

        return ActivityType::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
