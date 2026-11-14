{{--
    Meta tags and shared styles for an event page. Expects $event, $map, $summary.
--}}
@push('meta')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $event->title }} — Xplore Smithers">
<meta property="og:description" content="{{ $summary }}">
@if($event->cover_image_url)
<meta property="og:image" content="{{ url($event->cover_image_url) }}">
<meta property="og:image:alt" content="{{ $event->title }}">
@endif
<meta property="og:site_name" content="Xplore Smithers">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $event->title }} — Xplore Smithers">
<meta name="twitter:description" content="{{ $summary }}">
@if($event->cover_image_url)
<meta name="twitter:image" content="{{ url($event->cover_image_url) }}">
@endif
@endpush

@push('styles')
@include('tours._map-styles')
@if($event->isChristmasEvent())
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Mountains+of+Christmas:wght@700&display=swap" rel="stylesheet">
@endif
<style>
    /* Button & icon colour chosen in Settings → Events, with derived shades. */
    :root {
        --tour-primary: {{ $accentPalette['primary'] }};
        --tour-primary-dark: {{ $accentPalette['dark'] }};
        --tour-primary-soft: {{ $accentPalette['soft'] }};
        --tour-primary-border: {{ $accentPalette['border'] }};
        --event-on-accent: {{ $accentPalette['onPrimary'] }};
    }
    .event-page .event-accent-bg,
    .event-page .stop-number-badge,
    #tour-map .tour-marker-badge { color: var(--event-on-accent); }

    /* Solid fills on this page: no gradients on markers or stop badges. */
    #tour-map .tour-marker-badge { background: var(--tour-primary); }
    #tour-map .tour-marker.active .tour-marker-badge { background: var(--tour-accent); }
    .event-page .stop-number-badge { background: var(--tour-primary); box-shadow: none; }

    .event-page .stop-card { transition: border-color .15s ease, background-color .15s ease; }
    .event-page .stop-card:hover { border-color: var(--tour-primary-border); }
    .event-page .stop-card.active { border-color: var(--tour-primary); background-color: var(--tour-primary-soft); }
    .event-page .stop-card:focus-visible { outline: 2px solid var(--tour-primary); outline-offset: 2px; }

    /* Slim scrollbar in the theme colour, shown only while the pointer is over the list. */
    .event-scroll { scrollbar-width: thin; scrollbar-color: transparent transparent; }
    .event-scroll:hover, .event-scroll:focus-within { scrollbar-color: var(--tour-primary-border) transparent; }
    .event-scroll::-webkit-scrollbar { width: 4px; }
    .event-scroll::-webkit-scrollbar-track { background: transparent; }
    .event-scroll::-webkit-scrollbar-thumb { background-color: transparent; border-radius: 9999px; }
    .event-scroll:hover::-webkit-scrollbar-thumb,
    .event-scroll:focus-within::-webkit-scrollbar-thumb { background-color: var(--tour-primary-border); }
    .event-scroll::-webkit-scrollbar-thumb:hover { background-color: var(--tour-primary); }

    .event-accent-text { color: var(--tour-primary); }
    .event-accent-bg { background-color: var(--tour-primary); }
    .event-accent-bg:hover { background-color: var(--tour-primary-dark); }
    .event-accent-soft { background-color: var(--tour-primary-soft); }
    .event-accent-border { border-color: var(--tour-primary); }

    /* Section eyebrows in the brand orange (accent-600), or the event's festive colour. */
    .event-eyebrow { color: {{ $event->isChristmasEvent() ? 'var(--tour-primary)' : '#ea580c' }}; }
    @if($event->isChristmasEvent())
    .event-title-festive { font-family: 'Mountains of Christmas', 'Playfair Display', Georgia, serif; letter-spacing: 0.01em; }
    @endif
</style>
@if($event->isChristmasEvent())
@include('local-events.partials._christmas-styles')
@endif
@endpush
