@php
    $primaryMedia = $business->media->firstWhere('is_primary', true) ?? $business->media->where('media_type', 'photo')->first();
    $photoUrl = $primaryMedia?->url;
@endphp

<div class="trail-card group cursor-pointer hover-lift"
     onclick="window.location.href='{{ route('businesses.public.show', $business->slug) }}'">
    {{-- Image --}}
    <div class="trail-card-image group-hover:scale-105 transition-transform duration-500">
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $business->name }}" class="w-full h-full object-cover" loading="lazy">
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
                <span class="text-7xl opacity-60">{{ $business->icon }}</span>
            </div>
        @endif

        @if($business->is_featured)
            <div class="absolute top-3 left-3">
                <span class="badge bg-amber-400 text-amber-900 font-bold shadow-lg">⭐ Featured</span>
            </div>
        @endif

        <div class="absolute top-3 right-3">
            <span class="badge bg-blue-600 text-white font-semibold shadow-lg text-xs">
                {{ explode(' ', $business->business_type_label)[0] }}
                {{ implode(' ', array_slice(explode(' ', $business->business_type_label), 1)) }}
            </span>
        </div>

        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
            <div class="transform scale-0 group-hover:scale-100 transition-transform duration-300">
                <span class="bg-white text-gray-900 px-6 py-3 rounded-lg font-semibold shadow-lg">View Details</span>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="trail-card-body">
        <div class="mb-3">
            <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-accent-600 transition-colors">
                {{ $business->name }}
            </h3>
            @if($business->address)
                <p class="text-sm text-gray-500 flex items-center">
                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    {{ $business->address }}
                </p>
            @endif
        </div>

        @if($business->tagline)
            <p class="text-sm text-indigo-600 italic mb-3">{{ $business->tagline }}</p>
        @endif

        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
            {{ Str::limit(strip_tags($business->description), 120) }}
        </p>

        <div class="flex items-center justify-between">
            <a href="{{ route('businesses.public.show', $business->slug) }}"
               class="text-emerald-600 hover:text-emerald-700 font-semibold text-sm flex items-center">
                View Details
                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div class="flex items-center gap-2">
                @if($business->price_range && $business->price_range !== 'free')
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $business->price_range }}</span>
                @elseif($business->price_range === 'free')
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Free</span>
                @endif
                @if($business->is_seasonal)
                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200">🗓 Seasonal</span>
                @endif
            </div>
        </div>
    </div>
</div>
