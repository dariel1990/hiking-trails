{{--
    Pinned stops/activities; clicking one focuses it on the map (focusStop in tours._map-script).
    Expects $mappedStops (keys match the map payload indexes) and $isActivityMap.
    Optional $variant: 'cards' (default), 'schedule' (table-like rows) or 'numbered' (editorial list).
    Optional $listClass: classes for the list element.
    Optional $titleClass: classes for each stop's name (defaults to Inter; omit font-sans to use the site's Oswald h3).
--}}
@php
    $titleClass = $titleClass ?? 'font-sans text-[15px] font-semibold tracking-normal';
    $variant = $variant ?? 'cards';
    $total = $mappedStops->count();
    $noun = $isActivityMap ? 'Activity' : 'Stop';
@endphp
<ol class="{{ $listClass ?? ($variant === 'schedule' ? 'divide-y divide-gray-200' : 'grid gap-3') }}">
    @foreach($mappedStops as $stopIndex => $stop)
        @php
            $displayName = $stop->displayName($noun.' '.$loop->iteration);
            $coords = $stop->coordinates();
        @endphp
        <li id="stop-card-{{ $stopIndex }}" role="button" tabindex="0"
            onclick="focusStop({{ $stopIndex }}, @json($coords))"
            onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); this.click(); }"
            aria-label="Show {{ $displayName }} on the map"
            @class([
                'stop-card cursor-pointer',
                'rounded-xl border border-gray-200 bg-white p-4' => $variant === 'cards',
                'grid grid-cols-[5.5rem_1fr] gap-x-4 border-l-2 border-transparent px-5 py-4 sm:grid-cols-[7rem_1fr_auto]' => $variant === 'schedule',
                'grid grid-cols-[3rem_1fr] gap-x-4 border-t border-forest-800 py-5' => $variant === 'numbered',
            ])>

            @if($variant === 'schedule')
                <div class="text-sm font-semibold tabular-nums text-gray-900">{{ $stop->time_label ?: '—' }}</div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        @if($stop->hasIcon())
                            <span class="text-base leading-none" aria-hidden="true">
                                @if($stop->icon_image_url)<img src="{{ $stop->icon_image_url }}" alt="" class="h-5 w-5 object-contain">@else{{ $stop->icon }}@endif
                            </span>
                        @endif
                        <h3 class="font-sans text-[15px] font-semibold tracking-normal text-gray-900">{{ $displayName }}</h3>
                    </div>
                    @if($stop->description)
                        <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $stop->description }}</p>
                    @endif
                    @if($stop->notes)
                        <p class="mt-1 text-sm text-gray-500">{{ $stop->notes }}</p>
                    @endif
                </div>
                @if($coords)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $coords[0] }},{{ $coords[1] }}"
                        target="_blank" rel="noopener" onclick="event.stopPropagation()"
                        class="col-start-2 mt-2 inline-flex items-center gap-1 self-start text-sm font-semibold text-gray-700 hover:text-gray-900 sm:col-start-3 sm:mt-0">
                        @include('local-events.partials._icon', ['name' => 'directions', 'class' => 'h-4 w-4'])
                        Get Directions
                    </a>
                @endif

            @elseif($variant === 'numbered')
                <span class="font-display text-3xl font-semibold leading-none text-forest-300 lining-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <div class="min-w-0">
                    @if($stop->time_label)
                        <p class="event-eyebrow text-xs font-semibold uppercase tracking-[0.14em]">{{ $stop->time_label }}</p>
                    @endif
                    <h3 class="mt-1 font-display text-xl font-semibold leading-snug tracking-tight text-forest-900">
                        @if($stop->hasIcon() && ! $stop->icon_image_url)<span class="mr-1" aria-hidden="true">{{ $stop->icon }}</span>@endif{{ $displayName }}
                    </h3>
                    @if($stop->description)
                        <p class="mt-2 whitespace-pre-line text-[15px] leading-relaxed text-gray-600">{{ $stop->description }}</p>
                    @endif
                    @if($stop->notes)
                        <p class="mt-1 text-sm italic text-gray-500">{{ $stop->notes }}</p>
                    @endif
                    @if($coords)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $coords[0] }},{{ $coords[1] }}"
                            target="_blank" rel="noopener" onclick="event.stopPropagation()"
                            class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-forest-800 underline decoration-forest-300 underline-offset-4 hover:decoration-forest-800">
                            Get Directions
                        </a>
                    @endif
                </div>

            @else
                <div class="flex gap-4">
                    @include('tours._stop-badge', ['stop' => $stop, 'number' => $loop->iteration, 'inline' => true])
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                            <h3 class="{{ $titleClass }} text-gray-900">{{ $displayName }}</h3>
                            @if($stop->time_label)
                                <span class="event-accent-text text-xs font-semibold">{{ $stop->time_label }}</span>
                            @endif
                        </div>
                        @if($stop->description)
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-600 line-clamp-4">{{ $stop->description }}</p>
                        @endif
                        @if($stop->notes)
                            <p class="mt-1 text-sm text-gray-500">{{ $stop->notes }}</p>
                        @endif
                        @if($coords)
                            <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $coords[0] }},{{ $coords[1] }}"
                                    target="_blank" rel="noopener" onclick="event.stopPropagation()"
                                    class="inline-flex items-center gap-1 font-semibold text-gray-700 hover:text-gray-900">
                                    @include('local-events.partials._icon', ['name' => 'directions', 'class' => 'h-3.5 w-3.5'])
                                    Get Directions
                                </a>
                                <span class="text-gray-400">{{ $noun }} {{ $loop->iteration }} of {{ $total }}</span>
                            </div>
                        @endif
                    </div>
                    @if($stop->image_url)
                        <img src="{{ $stop->image_url }}" alt="{{ $displayName }}" loading="lazy"
                            class="h-16 w-16 flex-shrink-0 rounded-lg bg-gray-100 object-cover">
                    @endif
                </div>
            @endif
        </li>
    @endforeach
</ol>
