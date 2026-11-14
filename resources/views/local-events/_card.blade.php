<a href="{{ route('local-events.show', $event->slug) }}" class="tour-card group block rounded-2xl overflow-hidden bg-white shadow-md">

    <!-- Cover Image -->
    <div class="relative h-56 overflow-hidden bg-gradient-to-br from-forest-700 to-forest-900">
        @if($event->cover_image_url)
            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" loading="lazy"
                class="tour-card-img absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
        @else
            <div class="absolute inset-0 flex items-center justify-center {{ $event->isChristmasEvent() ? 'bg-red-700' : 'bg-emerald-600' }}">
                <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="Xplore Smithers"
                    class="w-32 h-32 object-contain">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        @endif

        <!-- Date badge + icon -->
        <div class="absolute top-3 left-3 flex items-start gap-2">
            <span class="flex flex-col items-center justify-center w-12 h-12 rounded-xl bg-white/95 shadow-md leading-none">
                <span class="text-[10px] font-semibold uppercase tracking-wide text-accent-600">{{ $event->event_date->format('M') }}</span>
                <span class="text-lg font-bold text-gray-900 tabular-nums">{{ $event->event_date->format('j') }}</span>
            </span>
            <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/90 backdrop-blur-sm shadow-md overflow-hidden">
                @if($event->icon_image_url)
                    <img src="{{ $event->icon_image_url }}" alt="" class="w-full h-full object-contain p-0.5">
                @else
                    <span class="text-2xl leading-none" aria-hidden="true">{{ $event->marker_icon }}</span>
                @endif
            </span>
            @if($event->is_featured)
                <span class="inline-flex items-center rounded-full bg-accent-500 px-2.5 py-1 text-xs font-semibold text-white shadow-sm self-center">
                    ★ Featured
                </span>
            @endif
        </div>

        @if(($event->stops_count ?? 0) > 0)
            <div class="absolute bottom-3 right-3">
                <span class="inline-flex items-center gap-1 rounded-full bg-black/50 backdrop-blur-sm px-2.5 py-1 text-xs font-semibold text-white">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    On the map
                </span>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-5 flex flex-col">
        <h2 class="text-base font-bold text-gray-900 mb-1 group-hover:text-forest-700 transition-colors leading-snug">
            {{ $event->title }}
        </h2>
        @if($event->tagline)
            <p class="text-sm text-gray-500 mb-3 leading-relaxed">{{ $event->tagline }}</p>
        @endif

        <div class="flex flex-wrap gap-1.5 mt-auto">
            <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $event->date_range_label }}
            </span>
            @if($event->time_range_label)
                <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $event->time_range_label }}
                </span>
            @endif
            @if($event->venue ?: $event->location)
                <span class="inline-flex items-center gap-1 rounded-md bg-forest-50 border border-forest-100 px-2 py-0.5 text-xs font-medium text-forest-700">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                    {{ $event->venue ?: $event->location }}
                </span>
            @endif
        </div>

        <div class="mt-4 pt-4 border-t border-gray-100">
            <span class="text-sm font-semibold text-forest-700 group-hover:text-accent-600 transition-colors flex items-center gap-1">
                View Event
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </div>
    </div>
</a>
