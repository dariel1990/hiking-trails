@foreach($trails as $trail)
@php
    $featuredUrl = $trail->featured_media_url;
    if (!$featuredUrl) {
        $firstPhoto = $trail->trailMedia->where('media_type', 'photo')->first();
        $featuredUrl = $firstPhoto ? $firstPhoto->getThumbnail() ?? $firstPhoto->getUrl() : null;
    }
    $isLake = $type === 'lakes';
@endphp
<div class="trail-card group cursor-pointer hover-lift"
     onclick="window.location.href='{{ route('trails.show', $trail->id) }}'">
    <div class="trail-card-image group-hover:scale-105 transition-transform duration-500">
        @if($featuredUrl)
            <img src="{{ $featuredUrl }}" alt="{{ $trail->name }}" class="w-full h-full object-cover">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-emerald-600">
                <img src="{{ asset('images/xplore-smithers-logo.png') }}" alt="Xplore Smithers" class="w-32 h-32 object-contain">
            </div>
        @endif
        @if($trail->is_featured)
            <div class="absolute top-3 left-3">
                <span class="badge bg-amber-400 text-amber-900 font-bold shadow-lg">⭐ Featured</span>
            </div>
        @endif
        <div class="absolute top-3 right-3">
            @if($isLake)
                <span class="badge bg-blue-500 text-white font-semibold shadow-lg">🐟 Fishing</span>
            @else
                <span class="difficulty-badge difficulty-{{ intval($trail->difficulty_level) }} shadow-lg">
                    {{ $trail->difficulty_level }}/5
                </span>
            @endif
        </div>
        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
            <div class="transform scale-0 group-hover:scale-100 transition-transform duration-300">
                <span class="bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold shadow-lg">
                    {{ $isLake ? 'View Lake' : 'Explore Trail' }}
                </span>
            </div>
        </div>
    </div>
    <div class="trail-card-body">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-accent-600 transition-colors">{{ $trail->name }}</h3>
            @if($trail->location)
                <p class="text-sm text-gray-500 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ $trail->location }}
                </p>
            @endif
        </div>
        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
            {{ Str::limit(strip_tags($trail->description), 120) }}
        </p>
        @if($isLake)
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center p-3 bg-emerald-50 rounded-lg">
                    <div class="text-lg font-bold text-emerald-600">{{ count($trail->fish_species ?? []) }}</div>
                    <div class="text-xs text-gray-500">species</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-600 capitalize">{{ $trail->best_fishing_season ?? '—' }}</div>
                    <div class="text-xs text-gray-500">best season</div>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-lg">
                    <div class="text-lg font-bold text-amber-600">{{ number_format($trail->view_count ?? 0) }}</div>
                    <div class="text-xs text-gray-500">views</div>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="badge-secondary">Fishing Lake</span>
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $trail->view_count }} views
                </div>
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('trails.show', $trail->id) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm flex items-center">
                    View Lake
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @if($trail->best_seasons)
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($trail->best_seasons, 0, 2) as $season)
                            <span class="season-{{ strtolower($season) }} text-xs px-2 py-1 rounded border">{{ $season }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-600">{{ $trail->distance_km }}</div>
                    <div class="text-xs text-gray-500">km</div>
                </div>
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-lg font-bold text-green-600">{{ $trail->elevation_gain_m }}</div>
                    <div class="text-xs text-gray-500">meters</div>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-lg">
                    <div class="text-lg font-bold text-amber-600">{{ $trail->estimated_time_hours }}</div>
                    <div class="text-xs text-gray-500">hours</div>
                </div>
            </div>
            <div class="flex items-center justify-between mb-4">
                <span class="badge-secondary">{{ ucwords(str_replace('-', ' ', $trail->trail_type)) }}</span>
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $trail->view_count }} adventurers
                </div>
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('trails.show', $trail->id) }}" class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm flex items-center">
                    Start Adventure
                    <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @if($trail->best_seasons)
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($trail->best_seasons, 0, 2) as $season)
                            <span class="season-{{ strtolower($season) }} text-xs px-2 py-1 rounded border">{{ $season }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
@endforeach
