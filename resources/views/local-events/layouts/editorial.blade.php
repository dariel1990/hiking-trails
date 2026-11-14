@extends('layouts.public')

{{-- Event page layout: Editorial / Magazine. Data comes from App\View\Composers\EventPageComposer. --}}


@section('title', $event->title.' — Xplore Smithers')
@section('meta_description', $summary)

@include('local-events.partials._head')

@section('content')
<div data-layout="editorial" class="event-page event-surface bg-sand-100 text-forest-900 lining-nums" data-theme="{{ $event->theme }}">
    @include('local-events.partials._christmas-lights')

    {{-- Split hero --}}
    <header class="grid lg:min-h-[calc(100vh-5rem)] lg:grid-cols-12">
        <div class="flex flex-col px-4 pb-12 pt-8 sm:px-8 lg:col-span-5 lg:px-12 lg:pb-16 xl:px-16">
            <a href="{{ route('local-events.index') }}" class="inline-flex items-center gap-1.5 self-start text-sm font-medium text-gray-600 hover:text-gray-900">
                @include('local-events.partials._icon', ['name' => 'back'])
                All Events
            </a>

            <div class="mt-12 flex flex-1 flex-col justify-center lg:mt-0">
                <p class="event-eyebrow flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em]">
                    <span class="event-accent-bg h-px w-10 flex-shrink-0"></span>
                    <span>{{ $event->category ?: 'Event' }} · {{ $event->event_date->format('F Y') }}@if($event->is_featured) · <span class="text-forest-800">★ Featured</span>@endif</span>
                </p>
                <h1 class="{{ $isChristmas ? 'event-title-festive text-5xl sm:text-7xl xl:text-8xl' : 'font-display text-5xl font-semibold sm:text-6xl xl:text-7xl' }} mt-6 leading-[0.98] tracking-tight text-forest-900">{{ $event->title }}</h1>
                @if($event->tagline)
                    <p class="mt-6 max-w-lg font-display text-xl italic leading-relaxed text-gray-600 lg:text-2xl">{{ $event->tagline }}</p>
                @endif
                @if($event->organizer)
                    <p class="mt-6 text-sm text-gray-500">Presented by <span class="font-semibold text-gray-900">{{ $event->organizer }}</span></p>
                @endif
                @if($event->has_passed)
                    <p class="mt-4 text-sm font-semibold uppercase tracking-[0.18em] text-gray-500">This event has ended</p>
                @endif

                <div class="mt-10 flex flex-wrap gap-3">
                    @if($event->external_url)
                        <a href="{{ $event->external_url }}" target="_blank" rel="noopener"
                            class="{{ $isChristmas ? 'event-accent-bg' : 'bg-forest-800 hover:bg-forest-900' }} inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold text-white">
                            More info
                            @include('local-events.partials._icon', ['name' => 'external'])
                        </a>
                    @endif
                    <a href="{{ route('events.calendar', $event->id) }}"
                        class="inline-flex items-center gap-2 border border-forest-800 px-6 py-3.5 text-sm font-semibold text-forest-900 hover:bg-forest-800 hover:text-white">
                        @include('local-events.partials._icon', ['name' => 'calendar'])
                        Add to Calendar
                    </a>
                    <button type="button" data-share-open="event-share" aria-label="Share this event"
                        class="inline-flex items-center gap-2 px-3 py-3.5 text-sm font-semibold text-gray-700 underline decoration-forest-300 underline-offset-4 hover:decoration-forest-800">
                        Share
                    </button>
                </div>
            </div>
        </div>

        <div class="relative min-h-[320px] sm:min-h-[460px] lg:col-span-7 lg:min-h-0">
            @if($event->cover_image_url)
                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="absolute inset-0 h-full w-full object-cover">
            @else
                <div class="event-accent-bg absolute inset-0 flex items-center justify-center text-8xl" aria-hidden="true">{{ $event->marker_icon }}</div>
            @endif
            @include('local-events.partials._christmas-snow')
        </div>
    </header>

    {{-- Facts band --}}
    <div class="border-y border-forest-800">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-8 lg:grid-cols-4 lg:px-12">
            <div class="col-span-2 flex items-center gap-5 border-b border-gray-300 py-6 lg:col-span-1 lg:border-b-0 lg:border-r lg:pr-8">
                <span class="font-display text-6xl font-semibold leading-none">{{ $event->event_date->format('d') }}</span>
                <div>
                    <dt class="sr-only">Date</dt>
                    <dd class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600">{{ $event->event_date->format('M Y') }}</dd>
                    <dd class="mt-1 text-sm font-medium">{{ $event->is_multi_day ? 'Until '.$event->end_date->format('M j') : $event->event_date->format('l') }}</dd>
                </div>
            </div>
            <div class="border-r border-gray-300 py-6 pr-4 lg:px-8">
                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Time</dt>
                <dd class="mt-2 font-display text-lg font-semibold">{{ $event->time_range_label ?: 'See details' }}</dd>
            </div>
            <div class="py-6 pl-4 lg:border-r lg:border-gray-300 lg:px-8">
                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Where</dt>
                <dd class="mt-2 font-display text-lg font-semibold">{{ $place ?: 'Smithers, BC' }}</dd>
                @if($event->venue && $event->location && $event->location !== $event->venue)
                    <dd class="text-sm text-gray-600">{{ $event->location }}</dd>
                @endif
            </div>
            <div class="col-span-2 border-t border-gray-300 py-6 lg:col-span-1 lg:border-t-0 lg:pl-8">
                <dt class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $stopCount ? 'Programme' : 'Hosted by' }}</dt>
                <dd class="mt-2 font-display text-lg font-semibold">
                    @if($stopCount){{ $stopCount }} {{ $stopNoun }}@if($event->route_distance_km) · {{ number_format($event->route_distance_km, 1) }} km route @endif
                    @else{{ $event->organizer ?: 'Xplore Smithers' }}@endif
                </dd>
            </div>
        </dl>
    </div>

    {{-- Story --}}
    @if($event->description || $event->video_url)
        <section class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-8 lg:grid-cols-12 lg:gap-16 lg:px-12 lg:py-24">
            <div class="lg:col-span-4">
                <h2 class="event-eyebrow font-sans text-xs font-semibold uppercase tracking-[0.22em] lg:text-xs">The story</h2>
                <dl class="event-accent-border mt-6 space-y-4 border-l-2 pl-6 text-sm">
                    @foreach(array_filter([
                        'When' => $event->date_range_label,
                        'Venue' => $place,
                        'Category' => $event->category,
                        'Organizer' => $event->organizer,
                    ]) as $label => $value)
                        <div>
                            <dt class="text-xs uppercase tracking-[0.16em] text-gray-500">{{ $label }}</dt>
                            <dd class="mt-0.5 font-display text-lg font-semibold leading-snug text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
            <div class="lg:col-span-8">
                @if($event->description)
                    @include('local-events.partials._description', [
                        'textClass' => 'text-lg leading-8 text-gray-800 first-letter:float-left first-letter:mr-3 first-letter:mt-1 first-letter:font-display first-letter:text-7xl first-letter:font-semibold first-letter:leading-[0.8] first-letter:text-forest-900',
                        'clampClass' => 'line-clamp-none',
                    ])
                @endif
                @if($event->video_url)
                    <div class="mt-10">@include('partials.video-embed', ['model' => $event, 'embedId' => 'event-video'])</div>
                @endif
            </div>
        </section>
    @endif

    {{-- Programme --}}
    @if($hasMap || $roamingStops->isNotEmpty())
        <section class="event-band border-t border-forest-800 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-8 lg:px-12 lg:py-24">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="event-eyebrow text-xs font-semibold uppercase tracking-[0.22em]">{{ $hasMap ? 'On the map' : 'Around the event' }}</p>
                        <h2 class="mt-3 font-display text-4xl font-semibold leading-tight lg:text-5xl">{{ $isActivityMap ? 'The programme' : 'The route' }}</h2>
                    </div>
                    @if($mappedStops->isNotEmpty())
                        <p class="max-w-xs text-sm text-gray-500">Select {{ $isActivityMap ? 'an activity' : 'a stop' }} to find it on the map.</p>
                    @endif
                </div>

                @if($hasMap)
                    <div class="mt-10">
                        @include('local-events.partials._map', ['frameClass' => 'h-[360px] border border-forest-800 sm:h-[480px] lg:h-[540px]'])
                    </div>
                @endif
                @if($mappedStops->isNotEmpty())
                    <div class="mt-12">
                        @include('local-events.partials._stops', ['variant' => 'numbered', 'listClass' => 'grid gap-x-12 md:grid-cols-2'])
                    </div>
                @endif
                @if($roamingStops->isNotEmpty())
                    <div class="mt-12 border-t border-forest-800 pt-6">
                        <h3 class="font-display text-2xl font-semibold tracking-tight lg:text-2xl">Also around downtown</h3>
                        <p class="mb-6 mt-1 text-sm text-gray-500">These happen all around the event — keep an eye out!</p>
                        @include('local-events.partials._roaming', ['listClass' => 'grid gap-3 md:grid-cols-2'])
                    </div>
                @endif
            </div>
        </section>
    @endif


    {{-- Gallery --}}
    @if($galleryMedia->isNotEmpty())
        <div class="border-t border-forest-800">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-8 lg:px-12 lg:py-24">
                @include('local-events.partials._gallery', ['layout' => 'mosaic', 'headingClass' => 'font-display text-4xl font-semibold leading-tight text-forest-900 lg:text-5xl'])
            </div>
        </div>
    @endif
    @include('local-events.partials._christmas-signoff')
</div>

@if($hasMap)
    @include('tours._map-script')
@endif
@include('local-events.partials._share')
@include('local-events.partials._layout-preview-bar')
@endsection
