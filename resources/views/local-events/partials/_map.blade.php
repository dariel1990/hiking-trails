{{--
    The event map with its reset and style controls; rendered by tours._map-script.
    Optional $frameClass: size, border and rounding of the map frame.
--}}
<div class="event-map-frame relative overflow-hidden bg-gray-100 {{ $frameClass ?? 'h-[420px] rounded-2xl border border-gray-200' }}">
    <div id="tour-map" class="h-full w-full"></div>

    <button type="button" id="tour-map-reset" title="Show the whole route"
        class="absolute bottom-3 left-3 z-10 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/></svg>
        Reset view
    </button>

    <div class="absolute right-14 top-3 z-10">
        <div class="relative">
            <button type="button" id="tour-layers-toggle" title="Change map style" aria-label="Change map style"
                class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm hover:bg-gray-50">
                <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0v10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2z"/></svg>
            </button>
            <div id="tour-layers-dropdown" class="absolute right-0 top-full mt-2 hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl" style="min-width:196px;">
                <div class="p-2.5">
                    <p class="px-2 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Map Style</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach([
                            ['mapbox://styles/mapbox/standard', 'standard', 'Standard'],
                            ['mapbox://styles/mapbox/satellite-streets-v12', 'satellite', 'Satellite'],
                            ['mapbox://styles/mapbox/outdoors-v12', 'terrain', 'Terrain'],
                            ['mapbox://styles/mapbox/navigation-day-v1', 'outdoor', 'Outdoors'],
                        ] as [$style, $preview, $label])
                            <button type="button" class="tour-layer-card {{ $preview === 'terrain' ? 'active' : '' }}" data-style="{{ $style }}">
                                <div class="layer-preview"><img src="{{ asset('images/map-layers/'.$preview.'.png') }}" alt="{{ $label }}" class="h-full w-full object-cover"></div>
                                <span class="layer-label">{{ $label }}</span>
                                <svg class="layer-checkmark" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
