@extends('layouts.admin')

@section('title', 'Media Library')
@section('page-title', 'Media Library')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Media Library</h2>
            <p class="text-sm text-muted-foreground">All photos and videos across trails, facilities, and businesses</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-6">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Total</p>
                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Photos</p>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['photos'] }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Videos</p>
                <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $stats['videos'] }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Trail Media</p>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['trail_media'] }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Facility Media</p>
                <svg class="h-4 w-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-orange-500">{{ $stats['facility_media'] }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Business Media</p>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['business_media'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.media.index') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="grid flex-1 gap-2">
                    <label class="text-sm font-medium leading-none">Search by trail / facility</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="e.g. Blue Lakes"
                            class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>

                <div class="grid gap-2 min-w-[150px]">
                    <label class="text-sm font-medium leading-none">Type</label>
                    <select name="type" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="all"   {{ $type === 'all'   ? 'selected' : '' }}>All types</option>
                        <option value="photo" {{ $type === 'photo' ? 'selected' : '' }}>Photos only</option>
                        <option value="video" {{ $type === 'video' ? 'selected' : '' }}>Videos only</option>
                    </select>
                </div>

                <div class="grid gap-2 min-w-[160px]">
                    <label class="text-sm font-medium leading-none">Source</label>
                    <select name="source" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="all"      {{ $source === 'all'      ? 'selected' : '' }}>All sources</option>
                        <option value="trail"    {{ $source === 'trail'    ? 'selected' : '' }}>Trails only</option>
                        <option value="facility" {{ $source === 'facility' ? 'selected' : '' }}>Facilities only</option>
                        <option value="business" {{ $source === 'business' ? 'selected' : '' }}>Businesses only</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        Filter
                    </button>
                    @if($search || $type !== 'all' || $source !== 'all')
                        <a href="{{ route('admin.media.index') }}"
                            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Trail Media Section --}}
    @if($trailMedia->isNotEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-transparent bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                        Trails
                    </span>
                    <span class="text-sm text-muted-foreground">{{ $trailMedia->count() }} item{{ $trailMedia->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach($trailMedia as $media)
                        @include('admin.media._card', ['media' => $media, 'mediaSource' => 'trail'])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Facility Media Section --}}
    @if($facilityMedia->isNotEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-transparent bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-800">
                        Facilities
                    </span>
                    <span class="text-sm text-muted-foreground">{{ $facilityMedia->count() }} item{{ $facilityMedia->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach($facilityMedia as $media)
                        @include('admin.media._card', ['media' => $media, 'mediaSource' => 'facility'])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Business Media Section --}}
    @if($businessMedia->isNotEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-transparent bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                        Businesses
                    </span>
                    <span class="text-sm text-muted-foreground">{{ $businessMedia->count() }} item{{ $businessMedia->count() !== 1 ? 's' : '' }}</span>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach($businessMedia as $media)
                        @include('admin.media._card', ['media' => $media, 'mediaSource' => 'business'])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Empty state --}}
    @if($trailMedia->isEmpty() && $facilityMedia->isEmpty() && $businessMedia->isEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="p-12 text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="rounded-full bg-muted p-3">
                        <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold">No media found</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ $search ? "No results for \"$search\"." : 'Upload photos or videos when editing a trail, facility, or business.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
