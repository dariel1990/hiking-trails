{{--
    "View on Interactive Map" button: opens /map on the Events tab, focused on this event.
    Shown only when the interactive map lists the event (it has something to map and hasn't ended).
    Optional $class: wrapper spacing/alignment; $buttonClass: shape of the button.
--}}
@if($hasMap && ! $event->has_passed)
    <div class="{{ $class ?? 'mt-4' }}">
        <a href="{{ route('map', ['event' => $event->urlKey()]) }}"
            class="event-accent-text event-accent-border inline-flex items-center gap-2 border px-4 py-2 text-sm font-semibold transition-colors hover:bg-[var(--tour-primary-soft)] {{ $buttonClass ?? 'rounded-full' }}">
            @include('local-events.partials._icon', ['name' => 'route'])
            View on Interactive Map
        </a>
    </div>
@endif
