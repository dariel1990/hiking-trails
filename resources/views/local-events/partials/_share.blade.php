{{-- Share dialog for the event; open it with data-share-open="event-share". Expects $event, $place. --}}
@include('partials.share-modal', [
    'shareId' => 'event-share',
    'flatHeader' => true,
    'kicker' => 'Share This Event',
    'title' => $event->title,
    'subtitle' => $event->tagline ?: $event->date_range_label,
    'shareText' => 'Check out the '.$event->title.' in Smithers, BC!'.($event->isChristmasEvent() ? ' 🎄' : ''),
    'emailSubject' => 'Check out this event: '.$event->title,
    'emailBody' => 'I found this event and thought you might be interested!'."\n\n".'Event: '.$event->title."\n".$event->date_range_label.($place ? "\n".$place : ''),
])
