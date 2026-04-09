@extends('layouts.public')

@section('title', $business->name . ' — Smithers Local Business')

@push('meta')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $business->name }} — {{ $business->business_type_label }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($business->description), 200) }}">
@if($business->media->isNotEmpty())
<meta property="og:image" content="{{ url($business->media->first()->url) }}">
@endif
<meta property="og:site_name" content="Trail Finder Smithers">
@endpush

@push('styles')
<style>
    .sticky-sidebar {
        position: sticky;
        top: 100px;
        align-self: flex-start;
    }

    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }

    .photo-grid-item {
        position: relative;
        aspect-ratio: 4/3;
        overflow: hidden;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .photo-grid-item:hover {
        transform: scale(1.02);
    }

    .photo-grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.95);
        z-index: 9999;
        padding: 2rem;
    }

    .lightbox.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lightbox-content {
        max-width: 90vw;
        max-height: 90vh;
        position: relative;
    }

    .lightbox-content img {
        max-width: 100%;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 0.5rem;
    }

    .lightbox-close {
        position: fixed;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .lightbox-close:hover {
        background: rgba(0,0,0,0.8);
        transform: scale(1.1);
    }

    .tab-nav {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .tab-button {
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }

    .tab-button:hover { color: #10b981; }

    .tab-button.active {
        color: #10b981;
        border-bottom-color: #10b981;
    }

    .tab-content { display: none; }
    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .rich-content p { margin-bottom: 1rem; line-height: 1.75; }
    .rich-content p:last-child { margin-bottom: 0; }
    .rich-content h2 { font-size: 1.25rem; font-weight: 700; margin: 1.5rem 0 0.5rem; }
    .rich-content h3 { font-size: 1.1rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
    .rich-content strong { font-weight: 600; }
    .rich-content em { font-style: italic; }
    .rich-content ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
    .rich-content ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    .rich-content li { margin-bottom: 0.25rem; }
    .rich-content a { color: #059669; text-decoration: underline; }

    #business-map { border-radius: 1rem; overflow: hidden; }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
@php
    $heroMedia = $business->media->firstWhere('is_primary', true) ?? $business->media->where('media_type', 'photo')->first();
    $heroUrl = $heroMedia?->url;
    $photos = $business->media->where('media_type', 'photo');
    $videos = $business->media->where('media_type', 'video_url');
@endphp

<div class="relative h-[55vh] bg-gray-900 overflow-hidden">
    @if($heroUrl)
        <img src="{{ $heroUrl }}" alt="{{ $business->name }}" class="w-full h-full object-cover">
    @else
        <div class="absolute inset-0 flex items-center justify-center hero-gradient">
            <span class="text-9xl opacity-40">{{ $business->icon }}</span>
        </div>
    @endif

    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

    <div class="absolute inset-0 flex items-end z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 w-full">
            <nav class="mb-6">
                <a href="{{ route('businesses.public.index') }}"
                   class="inline-flex items-center text-white/90 hover:text-white text-sm font-medium transition-colors group">
                    <svg class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                    </svg>
                    Back to Local Businesses
                </a>
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="flex-1">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-3 leading-tight">
                        {{ $business->name }}
                    </h1>
                    @if($business->tagline)
                        <p class="text-xl text-emerald-200 italic mb-3">{{ $business->tagline }}</p>
                    @endif
                    @if($business->address)
                        <p class="text-lg text-white/80 flex items-center">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $business->address }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    @if($business->is_featured)
                        <span class="px-4 py-2 bg-amber-400 text-amber-900 rounded-full font-bold text-sm shadow-lg">⭐ Featured</span>
                    @endif
                    <span class="px-5 py-2 bg-white/95 backdrop-blur text-blue-700 rounded-full font-bold text-base shadow-lg">
                        {{ $business->business_type_label }}
                    </span>
                    @if($business->price_range && $business->price_range !== 'free')
                        <span class="px-4 py-2 bg-white/95 backdrop-blur text-gray-700 rounded-full font-bold text-base shadow-lg">
                            {{ $business->price_range }}
                        </span>
                    @elseif($business->price_range === 'free')
                        <span class="px-4 py-2 bg-emerald-500 text-white rounded-full font-bold text-base shadow-lg">Free</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Content ── --}}
<section class="section bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-10">

            {{-- ── Left: Main Content ── --}}
            <div class="flex-1 min-w-0">

                {{-- Tabs --}}
                @php
                    $hasPhotos = $photos->isNotEmpty();
                    $hasVideos = $videos->isNotEmpty();
                    $hasMap    = $business->latitude && $business->longitude;
                    $hasHours  = !empty($business->opening_hours);
                @endphp

                <div class="tab-nav flex overflow-x-auto">
                    <button class="tab-button active flex-shrink-0" onclick="switchTab('overview', this)">Overview</button>
                    @if($hasPhotos)
                        <button class="tab-button flex-shrink-0" onclick="switchTab('photos', this)">
                            Photos <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $photos->count() }}</span>
                        </button>
                    @endif
                    @if($hasVideos)
                        <button class="tab-button flex-shrink-0" onclick="switchTab('videos', this)">
                            Videos <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $videos->count() }}</span>
                        </button>
                    @endif
                    @if($hasMap)
                        <button class="tab-button flex-shrink-0" onclick="switchTab('location', this)">Location</button>
                    @endif
                </div>

                {{-- Overview Tab --}}
                <div id="tab-overview" class="tab-content active">
                    @if($business->description)
                        <div class="rich-content text-gray-700 text-lg leading-relaxed mb-8">
                            {!! $business->description !!}
                        </div>
                    @endif

                    @if($hasHours)
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Opening Hours
                            </h3>
                            <div class="bg-gray-50 rounded-xl p-6 divide-y divide-gray-200">
                                @php
                                    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                                    $today = strtolower(now()->format('l'));
                                @endphp
                                @foreach($days as $day)
                                    @if(isset($business->opening_hours[$day]))
                                        <div class="flex justify-between items-center py-3 first:pt-0 last:pb-0 {{ $day === $today ? 'font-bold text-emerald-700' : 'text-gray-700' }}">
                                            <span class="capitalize">
                                                {{ $day }}
                                                @if($day === $today)
                                                    <span class="ml-2 text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Today</span>
                                                @endif
                                            </span>
                                            <span>{{ $business->opening_hours[$day] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($business->is_seasonal && $business->season_open)
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8 flex items-start gap-3">
                            <span class="text-2xl flex-shrink-0">🗓</span>
                            <div>
                                <p class="font-semibold text-amber-800">Seasonal Business</p>
                                <p class="text-amber-700 text-sm mt-1">Open: {{ $business->season_open }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Photos Tab --}}
                @if($hasPhotos)
                    <div id="tab-photos" class="tab-content">
                        <div class="photo-grid">
                            @foreach($photos as $photo)
                                <div class="photo-grid-item" onclick="openLightbox('{{ $photo->url }}', '{{ addslashes($photo->caption ?? $business->name) }}')">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? $business->name }}" loading="lazy">
                                    @if($photo->caption)
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3 opacity-0 hover:opacity-100 transition-opacity">
                                            <p class="text-white text-sm">{{ $photo->caption }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Videos Tab --}}
                @if($hasVideos)
                    <div id="tab-videos" class="tab-content">
                        <div class="grid md:grid-cols-2 gap-6">
                            @foreach($videos as $video)
                                <div class="rounded-xl overflow-hidden shadow-md bg-gray-100">
                                    <div class="aspect-video">
                                        <iframe src="{{ $video->embed_url }}"
                                                class="w-full h-full"
                                                frameborder="0"
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                        </iframe>
                                    </div>
                                    @if($video->caption)
                                        <p class="p-3 text-sm text-gray-600">{{ $video->caption }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Location Tab --}}
                @if($hasMap)
                    <div id="tab-location" class="tab-content">
                        <div id="business-map" class="w-full h-96 mb-4 bg-gray-100"></div>
                        <p class="text-gray-600 text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $business->address ?? 'Smithers, BC' }}
                        </p>
                    </div>
                @endif

            </div>

            {{-- ── Sidebar ── --}}
            <div class="lg:w-80 flex-shrink-0">
                <div class="sticky-sidebar space-y-5">

                    {{-- Action Buttons --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Get in Touch</h3>
                        <div class="space-y-3">
                            @if($business->phone)
                                <a href="tel:{{ $business->phone }}"
                                   class="flex items-center gap-3 w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-4 py-3 rounded-xl transition-colors">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $business->phone }}
                                </a>
                            @endif
                            @if($business->email)
                                <a href="mailto:{{ $business->email }}"
                                   class="flex items-center gap-3 w-full bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-4 py-3 rounded-xl transition-colors">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Us
                                </a>
                            @endif
                            @if($business->website)
                                <a href="{{ $business->website }}" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-3 w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-4 py-3 rounded-xl transition-colors">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                    </svg>
                                    Visit Website
                                </a>
                            @endif
                            @if($business->latitude && $business->longitude)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $business->latitude }},{{ $business->longitude }}"
                                   target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-3 w-full bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold px-4 py-3 rounded-xl transition-colors">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    Get Directions
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Social --}}
                    @if($business->facebook_url || $business->instagram_url)
                        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Follow Us</h3>
                            <div class="flex gap-3">
                                @if($business->facebook_url)
                                    <a href="{{ $business->facebook_url }}" target="_blank" rel="noopener noreferrer"
                                       class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        Facebook
                                    </a>
                                @endif
                                @if($business->instagram_url)
                                    <a href="{{ $business->instagram_url }}" target="_blank" rel="noopener noreferrer"
                                       class="flex items-center gap-2 bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 hover:opacity-90 text-white font-semibold px-4 py-2 rounded-lg transition-opacity text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                        </svg>
                                        Instagram
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Map on the Smithers map --}}
                    <a href="{{ route('map') }}?business={{ $business->id }}"
                       class="flex items-center gap-3 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-4 rounded-2xl transition-colors shadow-lg text-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        View on Interactive Map
                    </a>

                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── Related Businesses ── --}}
@if($related->isNotEmpty())
<section class="section bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-3 mb-8">
            <span class="text-2xl">{{ explode(' ', $business->business_type_label)[0] }}</span>
            <h2 class="text-2xl font-bold text-gray-800">More {{ implode(' ', array_slice(explode(' ', $business->business_type_label), 1)) ?: $business->business_type_label }}</h2>
            <div class="flex-1 h-px bg-gray-300"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach($related as $rel)
                @include('businesses._card', ['business' => $rel])
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── CTA ── --}}
<section class="section cta-section">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Explore More of Smithers</h2>
        <p class="text-xl text-emerald-100 mb-8">Trails, lakes, and local businesses — there's always more to discover.</p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('businesses.public.index') }}"
               class="bg-white text-emerald-600 hover:bg-emerald-50 font-semibold py-4 px-10 rounded-xl transition-colors text-lg shadow-xl hover:scale-105">
                🏪 All Local Businesses
            </a>
            <a href="{{ route('trails.index') }}"
               class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold py-4 px-10 rounded-xl transition-all duration-300 text-lg shadow-xl hover:scale-105">
                🥾 Explore Trails
            </a>
        </div>
    </div>
</section>

{{-- Lightbox --}}
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">✕</span>
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <img id="lightbox-img" src="" alt="">
    </div>
</div>

@if($hasMap)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabLocation = document.getElementById('tab-location');
        let mapInitialized = false;

        function initMap() {
            if (mapInitialized) { return; }
            mapInitialized = true;
            const lat = {{ $business->latitude }};
            const lng = {{ $business->longitude }};
            const map = L.map('business-map').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            const icon = L.divIcon({
                html: '<div style="font-size:32px;line-height:1;">{{ $business->icon }}</div>',
                className: '',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
            });
            L.marker([lat, lng], { icon })
                .addTo(map)
                .bindPopup('<strong>{{ addslashes($business->name) }}</strong>@if($business->address)<br>{{ addslashes($business->address) }}@endif')
                .openPopup();
        }

        // Init map when Location tab becomes active
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', function () {
                if (document.getElementById('tab-location')?.classList.contains('active')) {
                    setTimeout(initMap, 50);
                }
            });
        });
    });
</script>
@endpush
@endif

<script>
    function switchTab(name, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }

    function openLightbox(url, caption) {
        document.getElementById('lightbox-img').src = url;
        document.getElementById('lightbox-img').alt = caption;
        document.getElementById('lightbox').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeLightbox(); }
    });
</script>

@endsection
