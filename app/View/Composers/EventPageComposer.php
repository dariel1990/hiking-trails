<?php

namespace App\View\Composers;

use App\Models\Event;
use App\Models\EventMedia;
use App\Models\EventStop;
use App\Support\AccentPalette;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Shared data for every event page layout (local-events.layouts.*), so each
 * layout only decides how the event looks, not what it shows.
 */
class EventPageComposer
{
    public function compose(View $view): void
    {
        /** @var Event $event */
        $event = $view->getData()['event'];

        // Roaming activities (no fixed spot) are listed separately and get no map pin.
        $mappedStops = $event->stops->reject(fn (EventStop $stop) => $stop->is_roaming);
        $isActivityMap = $event->stops->contains(fn (EventStop $stop) => $stop->hasIcon() || $stop->is_roaming);
        $stopCount = $event->stops->count();
        $galleryMedia = $this->galleryMedia($event);

        $view->with([
            'isChristmas' => $event->isChristmasEvent(),
            'hasMap' => $event->hasMap(),
            'mappedStops' => $mappedStops,
            'roamingStops' => $event->stops->filter(fn (EventStop $stop) => $stop->is_roaming),
            'isActivityMap' => $isActivityMap,
            'stopsHeading' => $isActivityMap ? 'Activities' : 'Route Stops',
            'stopCount' => $stopCount,
            'stopNoun' => $isActivityMap
                ? 'activit'.($stopCount === 1 ? 'y' : 'ies')
                : 'stop'.($stopCount === 1 ? '' : 's'),
            'place' => $event->venue ?: $event->location,
            'summary' => Str::limit($event->tagline ?: strip_tags($event->description ?? ''), 200),
            'accentPalette' => AccentPalette::fromHex($event->accentColor()),
            'galleryMedia' => $galleryMedia,
            'galleryTitle' => $this->galleryTitle($galleryMedia),
        ]);
    }

    /**
     * Gallery items in display order, with the first video moved to the front
     * so it gets the gallery's big card.
     *
     * @return Collection<int, EventMedia>
     */
    private function galleryMedia(Event $event): Collection
    {
        $video = $event->media->first(fn (EventMedia $media) => $media->isVideo());

        if (! $video) {
            return $event->media;
        }

        return $event->media->reject(fn (EventMedia $media) => $media->is($video))->prepend($video)->values();
    }

    /**
     * "Photos", "Videos" or "Photos & Videos", depending on what the gallery holds.
     *
     * @param  Collection<int, EventMedia>  $media
     */
    private function galleryTitle(Collection $media): string
    {
        $hasPhotos = $media->contains(fn (EventMedia $item) => $item->isPhoto());
        $hasVideos = $media->contains(fn (EventMedia $item) => $item->isVideo());

        return match (true) {
            $hasPhotos && $hasVideos => 'Photos & Videos',
            $hasVideos => 'Videos',
            default => 'Photos',
        };
    }
}
