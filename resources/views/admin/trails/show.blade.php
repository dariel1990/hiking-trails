@extends('layouts.admin')

@section('title', $trail->name)
@section('page-title', $trail->isFishingLake() ? 'Fishing Lake Details' : 'Trail Details')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="space-y-2">
        <div class="flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('admin.trails.index') }}" class="hover:text-foreground transition-colors">Trails</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span>{{ $trail->name }}</span>
        </div>
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight">{{ $trail->name }}</h1>
                <p class="text-muted-foreground">{{ $trail->location ?? 'Location not specified' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.trails.edit', $trail) }}" 
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit {{ $trail->isFishingLake() ? 'Fishing Lake' : 'Trail' }}
                </a>
                
                <a href="{{ route('trails.show', $trail) }}" target="_blank"
                class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Live
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Map -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">{{ $trail->isFishingLake() ? 'Location' : 'Trail Route' }}</h3>
                    <div id="trail-map" class="w-full h-96 rounded-md border border-input bg-muted z-10 relative">
                        <div id="map-loading" class="absolute inset-0 flex items-center justify-center bg-muted">
                            <div class="text-center">
                                <svg class="animate-spin w-8 h-8 mx-auto mb-2 text-primary" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                <p class="text-sm text-muted-foreground">Loading map...</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <button id="fit-route-btn" 
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3"
                                {{ !$trail->route_coordinates && !$trail->start_coordinates ? 'disabled' : '' }}>
                            {{ $trail->isFishingLake() ? 'Center Location' : 'Center Route' }}
                        </button>
                        <div class="text-sm text-muted-foreground">
                            @if($trail->route_coordinates)
                                <span class="text-green-600">{{ $trail->isFishingLake() ? 'Location set' : 'Route available' }}</span>
                            @else
                                <span class="text-orange-600">{{ $trail->isFishingLake() ? 'No location data' : 'No route data' }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Description</h3>
                    @if($trail->description)
                        <p class="text-muted-foreground leading-relaxed">{{ $trail->description }}</p>
                    @else
                        <div class="text-center py-8 text-muted-foreground">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm">No description available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Information -->
            @if($trail->directions || $trail->parking_info || $trail->safety_notes)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Trail Information</h3>
                    
                    <div class="space-y-6">
                        @if($trail->directions)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Directions
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->directions }}</p>
                        </div>
                        @endif

                        @if($trail->parking_info)
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                                </svg>
                                Parking
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->parking_info }}</p>
                        </div>
                        @endif

                        @if($trail->safety_notes)
                        <div class="border-l-4 border-red-500 pl-4">
                            <h4 class="font-medium mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Safety Notes
                            </h4>
                            <p class="text-muted-foreground">{{ $trail->safety_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Photos & Videos Section -->
            @php
                // Get only general trail media (excludes feature-linked media)
                $generalMedia = $trail->generalMedia;
            @endphp
            @if($generalMedia->count() > 0)
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Media ({{ $generalMedia->count() }})</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($generalMedia as $media)
                            @if($media->media_type === 'photo')
                                {{-- Photo Display --}}
                                <div class="relative group overflow-hidden rounded-lg border border-input cursor-pointer"
                                    onclick="openMediaModal('{{ asset('storage/' . $media->storage_path) }}', 'photo', '{{ $media->caption ?? $trail->name }}')">
                                    <img src="{{ asset('storage/' . $media->storage_path) }}" 
                                        alt="{{ $media->caption ?? $trail->name }}" 
                                        class="w-full h-32 object-cover transition-transform group-hover:scale-105">
                     @if($media->is_featured)
                                         <div class="absolute top-2 right-2">
                                             <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium shadow-sm">
                                                 <span class="sr-only">Featured:</span> Featured
                                             </span>
                                         </div>
                                     @endif
                                    @if($media->caption)
                                        <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white p-2">
                                            <p class="text-xs">{{ $media->caption }}</p>
                                        </div>
                                    @endif
                                </div>
                            @elseif($media->media_type === 'video_url')
                                {{-- Video URL Display --}}
                                <div class="relative group overflow-hidden rounded-lg border border-input cursor-pointer"
                                    onclick="openMediaModal('{{ $media->video_url }}', 'video', '{{ $media->caption ?? $trail->name }}')">
                                    <div class="w-full h-32 bg-gray-900 flex items-center justify-center relative">
                                        {{-- Video Thumbnail from provider --}}
                                        @if($media->video_provider === 'youtube')
                                            @php
                                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->video_url, $matches);
                                                $videoId = $matches[1] ?? null;
                                            @endphp
                                            @if($videoId)
                                                <img src="https://img.youtube.com/vi/{{ $videoId }}/mqdefault.jpg" 
                                                    alt="Video thumbnail"
                                                    class="w-full h-full object-cover">
                                            @endif
                                        @elseif($media->video_provider === 'vimeo')
                                            @php
                                                preg_match('/vimeo\.com\/(\d+)/', $media->video_url, $matches);
                                                $videoId = $matches[1] ?? null;
                                            @endphp
                                            {{-- Vimeo thumbnail using their image service --}}
                                            @if($videoId)
                                                <img src="https://vumbnail.com/{{ $videoId }}.jpg"
                                                    alt="Vimeo video thumbnail"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.parentElement.querySelector('.vimeo-fallback').style.display='flex';">
                                                <div class="vimeo-fallback absolute inset-0 flex items-center justify-center bg-gray-800 hidden">
                                                    <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="absolute inset-0 flex items-center justify-center bg-gray-800">
                                                    <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        @else
                                            {{-- Generic video icon --}}
                                            <div class="absolute inset-0 flex items-center justify-center bg-gray-800">
                                                <svg class="w-12 h-12 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        {{-- Play button overlay --}}
                                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                            <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg">
                                                <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Video badge --}}
                                    <div class="absolute top-2 left-2">
                                        <span class="bg-red-600 text-white px-2 py-1 rounded text-xs font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                            </svg>
                                            Video
                                        </span>
                                    </div>
                                    
                                     @if($media->is_featured)
                                         <div class="absolute top-2 right-2">
                                             <span class="bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium shadow-sm">
                                                 <span class="sr-only">Featured:</span> Featured
                                             </span>
                                         </div>
                                     @endif
                                    
                                    @if($media->caption)
                                        <div class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-white p-2">
                                            <p class="text-xs">{{ $media->caption }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            @if($trail->isFishingLake())
            <!-- Fishing Lake Overview -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <!-- Header with Location Type -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">Fishing Lake</h3>
                         @php
                             $statusConfig = [
                                 'active' => ['class' => 'bg-green-100 text-green-800 border border-green-200', 'label' => 'Active'],
                                 'closed' => ['class' => 'bg-red-100 text-red-800 border border-red-200', 'label' => 'Closed'],
                                 'seasonal' => ['class' => 'bg-amber-100 text-amber-800 border border-amber-200', 'label' => 'Seasonal']
                             ];
                             $config = $statusConfig[$trail->status] ?? ['class' => 'bg-gray-100 text-gray-800 border border-gray-200', 'label' => ucfirst($trail->status)];
                         @endphp
                         <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $config['class'] }}">
                             {{ $config['label'] }}
                         </span>
                    </div>

                    <!-- Location Info Card -->
                    @if($trail->fishing_location || $trail->fishing_distance_from_town)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-5">
                        @if($trail->fishing_location)
                        <div class="mb-2">
                            <div class="text-xs text-blue-600 font-medium uppercase tracking-wide mb-1">Location</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $trail->fishing_location }}</div>
                        </div>
                        @endif
                        @if($trail->fishing_distance_from_town)
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div class="text-sm text-gray-700">{{ $trail->fishing_distance_from_town }}</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Fish Species -->
                    @if($trail->fish_species && count($trail->getFishSpeciesList()) > 0)
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Fish Species</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($trail->getFishSpeciesList() as $species)
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-sm font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    {{ $species }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Best Fishing Info Grid -->
                    @if($trail->best_fishing_season || $trail->best_fishing_time)
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        @if($trail->best_fishing_season)
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="text-xs font-medium text-amber-700 uppercase">Best Season</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900 capitalize">{{ $trail->best_fishing_season }}</div>
                        </div>
                        @endif

                        <!-- Views - Always show beside Best Season -->
                        <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <svg class="w-3.5 h-3.5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span class="text-xs font-medium text-cyan-700 uppercase">Views</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">{{ number_format($trail->view_count ?? 0) }}</div>
                        </div>

                        @if($trail->best_fishing_time)
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs font-medium text-indigo-700 uppercase">Best Time</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900 capitalize">{{ $trail->best_fishing_time }}</div>
                        </div>
                        @endif

                        @if($trail->is_featured)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-center gap-1.5 mb-1">
                                <svg class="w-3.5 h-3.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-xs font-medium text-yellow-700 uppercase">Featured</span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">Yes</div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @else
            <!-- Trail Statistics -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Trail Statistics</h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            @if($trail->difficulty_level)
                            <div class="text-center p-3 bg-muted rounded-lg {{ !$trail->distance_km && !$trail->elevation_gain_m && !$trail->estimated_time_hours ? 'col-span-2' : '' }}">
                                <div class="text-2xl font-bold text-blue-600">{{ $trail->difficulty_level }}</div>
                                <div class="text-sm text-muted-foreground">Difficulty</div>
                            </div>
                            @endif

                            @if($trail->distance_km)
                            <div class="text-center p-3 bg-muted rounded-lg {{ !$trail->difficulty_level && !$trail->elevation_gain_m && !$trail->estimated_time_hours ? 'col-span-2' : '' }}">
                                <div class="text-2xl font-bold text-green-600">{{ $trail->distance_km }}</div>
                                <div class="text-sm text-muted-foreground">km</div>
                            </div>
                            @endif

                            @if($trail->elevation_gain_m)
                            <div class="text-center p-3 bg-muted rounded-lg {{ !$trail->difficulty_level && !$trail->distance_km && !$trail->estimated_time_hours ? 'col-span-2' : '' }}">
                                <div class="text-2xl font-bold text-orange-600">{{ $trail->elevation_gain_m }}</div>
                                <div class="text-sm text-muted-foreground">elevation (m)</div>
                            </div>
                            @endif

                            @if($trail->estimated_time_hours)
                            <div class="text-center p-3 bg-muted rounded-lg {{ !$trail->difficulty_level && !$trail->distance_km && !$trail->elevation_gain_m ? 'col-span-2' : '' }}">
                                <div class="text-2xl font-bold text-purple-600">{{ $trail->estimated_time_hours }}</div>
                                <div class="text-sm text-muted-foreground">hours</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Details</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Location Type</span>
                            <span class="font-medium capitalize">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    Trail
                                </span>
                            </span>
                        </div>

                        @if($trail->trail_type)
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Trail Type</span>
                            <span class="font-medium capitalize">{{ str_replace('-', ' ', $trail->trail_type) }}</span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Status</span>
                             @php
                                 $statusConfig = [
                                     'active' => ['class' => 'bg-green-100 text-green-800 border border-green-200', 'label' => 'Active'],
                                     'closed' => ['class' => 'bg-red-100 text-red-800 border border-red-200', 'label' => 'Closed'],
                                     'seasonal' => ['class' => 'bg-amber-100 text-amber-800 border border-amber-200', 'label' => 'Seasonal']
                                 ];
                                 $config = $statusConfig[$trail->status] ?? ['class' => 'bg-gray-100 text-gray-800 border border-gray-200', 'label' => ucfirst($trail->status)];
                             @endphp
                             <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $config['class'] }}">
                                 {{ $config['label'] }}
                             </span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Views</span>
                            <span class="font-medium">{{ number_format($trail->view_count) }}</span>
                        </div>

                        @if($trail->is_featured)
                        <div class="flex items-center justify-between py-2 border-b border-border">
                            <span class="text-muted-foreground">Featured</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">
                                Yes
                            </span>
                        </div>
                        @endif

                        @if($trail->best_seasons)
                        <div class="py-2">
                            <span class="text-muted-foreground block mb-2">Best Seasons</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($trail->best_seasons as $season)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $season }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    <div class="space-y-2">
                        <button onclick="window.open('{{ route('map') }}?trail={{ $trail->id }}', '_blank')" 
                                class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                            Show on Map
                        </button>
                        
                        @if($trail->start_coordinates)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $trail->start_coordinates[0] }},{{ $trail->start_coordinates[1] }}" 
                           target="_blank"
                           class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                            </svg>
                            Get Directions
                        </a>
                        @endif
                        
                        <button type="button" 
                                onclick="openDeleteModal()"
                                class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-3">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete {{ $trail->isFishingLake() ? 'Fishing Lake' : 'Trail' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="media-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="media-modal-title">
    <div class="relative max-w-5xl w-full bg-white rounded-lg shadow-xl max-h-[90vh] overflow-y-auto">
        <!-- Close button -->
        <button onclick="closeMediaModal()" 
                class="absolute top-4 right-4 z-10 bg-gray-900 bg-opacity-75 hover:bg-opacity-100 text-white rounded-full p-2 transition-all focus:outline-none focus:ring-2 focus:ring-white"
                aria-label="Close media viewer">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Hidden title for accessibility -->
        <h2 id="media-modal-title" class="sr-only">Media Viewer</h2>

        <!-- Content container -->
        <div id="modal-content" class="p-4">
            <!-- Content will be dynamically inserted here -->
        </div>

        <!-- Caption -->
        <div id="modal-caption" class="px-6 pb-6 text-center text-gray-700">
            <!-- Caption will be dynamically inserted here -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="relative max-w-md w-full bg-white rounded-lg shadow-xl">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 id="delete-modal-title" class="text-lg font-semibold text-gray-900">Delete {{ $trail->isFishingLake() ? 'Fishing Lake' : 'Trail' }}</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Are you sure you want to delete "{{ $trail->name }}"? This action cannot be undone and all associated data, including photos, videos, and features will be permanently removed.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
                <button onclick="closeDeleteModal()"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4">
                    Cancel
                </button>
                <form method="POST" action="{{ route('admin.trails.destroy', $trail) }}" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-4">
                        Delete {{ $trail->isFishingLake() ? 'Fishing Lake' : 'Trail' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trail = @json($trail);

        // Check if this is a fishing lake
        const isFishingLake = trail.location_type === 'fishing_lake';

        // Initialize map - fishing lakes zoom out more to see the whole lake
        const initialZoom = isFishingLake ? 11 : 13;
        const map = L.map('trail-map').setView(trail.start_coordinates || [49.2827, -122.7927], initialZoom);

        // Hide loading spinner once map tiles start loading
        map.on('load', function() {
            const loadingEl = document.getElementById('map-loading');
            if (loadingEl) loadingEl.style.display = 'none';
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let trailRoute = null;

        // Add trail route if available (only for trails, not fishing lakes)
        if (!isFishingLake && trail.route_coordinates && trail.route_coordinates.length > 0) {
            trailRoute = L.polyline(trail.route_coordinates, {
                color: '#3B82F6',
                weight: 4,
                opacity: 0.8,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(map);

            map.fitBounds(trailRoute.getBounds(), { padding: [20, 20] });
        }

        // Start marker (for trails) or Lake marker (for fishing lakes)
        if (trail.start_coordinates) {
            let markerIcon;
            let popupContent;

            if (isFishingLake) {
                // Fishing lake marker with fish icon (using emoji like create.blade.php)
                markerIcon = L.divIcon({
                    html: '<div class="bg-blue-500 text-white rounded-full w-10 h-10 flex items-center justify-center border-2 border-white shadow-lg text-2xl">🐟</div>',
                    className: 'custom-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });

                // Fishing lake popup with more relevant info
                const fishSpecies = trail.fish_species ? JSON.parse(trail.fish_species).join(', ') : 'Various species';
                popupContent = '<div class="text-center min-w-[140px]"><b>' + trail.name + '</b><div class="text-sm text-gray-600 mt-1">Fishing Lake</div>' +
                    (trail.fishing_location ? '<div class="text-xs text-gray-500 mt-1">' + trail.fishing_location + '</div>' : '') +
                    '<div class="text-xs text-blue-600 mt-2 font-medium">' + fishSpecies + '</div></div>';
            } else {
                // Trail start marker
                markerIcon = L.divIcon({
                    html: '<div class="bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">S</div>',
                    className: 'custom-marker',
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                popupContent = '<div class="text-center"><b>Trail Start</b><br><span class="text-sm">' + trail.name + '</span></div>';
            }

            L.marker(trail.start_coordinates, { icon: markerIcon })
                .addTo(map)
                .bindPopup(popupContent);
        }
        
        // End marker if different from start
        if (trail.end_coordinates && 
            JSON.stringify(trail.start_coordinates) !== JSON.stringify(trail.end_coordinates)) {
            
            const endIcon = L.divIcon({
                html: '<div class="bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm border-2 border-white shadow-lg">E</div>',
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            L.marker(trail.end_coordinates, { icon: endIcon })
                .addTo(map)
                .bindPopup('<div class="text-center"><b>Trail End</b></div>');
        }

        // Load trail features/highlights
        const features = @json($trail->features ?? []);

        if (features && features.length > 0) {
            features.forEach(feature => {
                // Handle coordinates properly
                let coords;
                if (Array.isArray(feature.coordinates)) {
                    coords = feature.coordinates;
                } else if (feature.coordinates && feature.coordinates.lat) {
                    coords = [feature.coordinates.lat, feature.coordinates.lng];
                } else {
                    return; // Skip invalid coordinates
                }
                
                // Create custom icon for feature
                const featureIcon = L.divIcon({
                    html: `<div style="background-color: ${feature.color || '#6366f1'};" class="w-10 h-10 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xl">${feature.icon || '📍'}</div>`,
                    className: 'custom-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                });
                
                // Get feature media if available
                // Build media thumbnails HTML (ALL media, not just primary)
                let mediaThumbnailsHTML = '';
                if (feature.media && feature.media.length > 0) {
                    const thumbnails = feature.media.map((media, index) => {
                        if (media.media_type === 'photo' && media.storage_path) {
                            // Photo thumbnail
                            return `
                                <div class="inline-block cursor-pointer hover:opacity-80 transition-opacity" 
                                     onclick="openMediaModal('{{ asset('storage/') }}/${media.storage_path}', 'photo', '${feature.name}')">
                                    <img src="{{ asset('storage/') }}/${media.storage_path}" 
                                         alt="${feature.name}"
                                         class="w-16 h-16 object-cover rounded border border-gray-200"
                                         title="Click to view larger">
                                </div>
                            `;
                        } else if (media.media_type === 'video_url' && media.video_url) {
                            // Video thumbnail
                            let thumbUrl = '';
                            if (media.video_provider === 'youtube') {
                                const youtubeMatch = media.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                                if (youtubeMatch) {
                                    thumbUrl = `https://img.youtube.com/vi/${youtubeMatch[1]}/mqdefault.jpg`;
                                }
                            }
                            
                            const thumbContent = thumbUrl 
                                ? `<img src="${thumbUrl}" class="w-16 h-16 object-cover rounded border border-gray-200">`
                                : `<div class="w-16 h-16 bg-gray-800 flex items-center justify-center rounded border border-gray-200">
                                       <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                           <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                       </svg>
                                   </div>`;
                            
                            return `
                                <div class="inline-block cursor-pointer hover:opacity-80 transition-opacity relative" 
                                     onclick="openMediaModal('${media.video_url}', 'video', '${feature.name}')">
                                    ${thumbContent}
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <div class="bg-white bg-opacity-90 rounded-full p-1 shadow">
                                            <svg class="w-4 h-4 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        return '';
                    }).filter(Boolean).join('');
                    
                    if (thumbnails) {
                        mediaThumbnailsHTML = `
                            <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
                                ${thumbnails}
                            </div>
                        `;
                    }
                }
                
                // Create popup content with media BELOW description
                const popupContent = `
                    <div class="min-w-[220px] max-w-[280px]">
                        <div class="space-y-2">
                            <div class="flex items-start gap-2">
                                <div style="background-color: ${feature.color || '#6366f1'};" class="w-7 h-7 rounded-lg flex items-center justify-center text-white shadow-sm flex-shrink-0">
                                    ${feature.icon || '📍'}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm text-gray-900 leading-tight mb-1">${feature.name}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                        ${(feature.feature_type || '').replace(/_/g, ' ')}
                                    </span>
                                </div>
                            </div>
                            ${feature.description ? `
                                <p class="text-xs text-gray-600 leading-relaxed pt-2 border-t border-gray-100">
                                    ${feature.description}
                                </p>
                            ` : ''}
                            ${mediaThumbnailsHTML}
                        </div>
                    </div>
                `;
                
                // Add marker to map
                L.marker(coords, { icon: featureIcon })
                    .addTo(map)
                    .bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'feature-popup'
                    });
            });
        }
        
        // Fit route button
        document.getElementById('fit-route-btn').addEventListener('click', function() {
            if (trailRoute) {
                map.fitBounds(trailRoute.getBounds(), { padding: [20, 20] });
            } else if (trail.start_coordinates) {
                map.setView(trail.start_coordinates, 13);
            }
        });
    });
</script>

<script>
function openMediaModal(url, type, caption) {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('modal-content');
    const captionEl = document.getElementById('modal-caption');
    
    if (type === 'photo') {
        content.innerHTML = `<img src="${url}" alt="${caption}" class="w-full h-auto max-h-[70vh] object-contain rounded-lg">`;
    } else if (type === 'video') {
        // Convert video URL to embed URL
        let embedUrl = '';
        
        // YouTube
        const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
        if (youtubeMatch) {
            embedUrl = `https://www.youtube.com/embed/${youtubeMatch[1]}`;
        }
        
        // Vimeo
        const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
        if (vimeoMatch) {
            embedUrl = `https://player.vimeo.com/video/${vimeoMatch[1]}`;
        }
        
        if (embedUrl) {
            content.innerHTML = `
                <div class="relative" style="padding-bottom: 56.25%; height: 0;">
                    <iframe src="${embedUrl}" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen
                            class="absolute top-0 left-0 w-full h-full rounded-lg">
                    </iframe>
                </div>
            `;
        }
    }
    
    captionEl.textContent = caption;
    modal.classList.remove('hidden');
}

function closeMediaModal() {
    const modal = document.getElementById('media-modal');
    const content = document.getElementById('modal-content');
    
    modal.classList.add('hidden');
    content.innerHTML = ''; // Clear content to stop video playback
}

// Close modal when clicking outside
document.getElementById('media-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeMediaModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMediaModal();
        closeDeleteModal();
    }
});

// Delete modal functions
function openDeleteModal() {
    const modal = document.getElementById('delete-modal');
    modal.classList.remove('hidden');
    // Focus the cancel button for accessibility
    setTimeout(() => {
        const cancelBtn = modal.querySelector('button[onclick="closeDeleteModal()"]');
        if (cancelBtn) cancelBtn.focus();
    }, 100);
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-modal');
    modal.classList.add('hidden');
}
</script>

<style>
/* shadcn/ui color variables */
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
  --radius: 0.5rem;
}

.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.bg-destructive { background-color: hsl(var(--destructive)); }
.text-destructive-foreground { color: hsl(var(--destructive-foreground)); }
.hover\:bg-destructive\/90:hover { background-color: hsl(var(--destructive) / 0.9); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }
</style>
@endpush
@endsection