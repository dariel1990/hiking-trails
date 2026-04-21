@extends('layouts.admin')

@section('title', 'Edit Business')
@section('page-title', 'Edit Business')

@section('content')

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.businesses.index') }}"
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Edit Business</h2>
            <p class="text-sm text-muted-foreground">{{ $business->name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.businesses.update', $business) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.businesses._form', ['business' => $business])
    </form>

    {{-- Existing Media --}}
    @if($business->media->isNotEmpty())
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6 border-b">
                <h3 class="text-lg font-semibold leading-none tracking-tight">Existing Media</h3>
                <p class="text-sm text-muted-foreground">{{ $business->media->count() }} item{{ $business->media->count() !== 1 ? 's' : '' }}</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach($business->media as $media)
                        <div class="group relative overflow-hidden rounded-lg border bg-card shadow-sm">
                            <div class="relative aspect-square bg-muted overflow-hidden">
                                @if($media->media_type === 'photo' && $media->file_path)
                                    <img src="{{ asset('storage/' . $media->file_path) }}"
                                        alt="{{ $media->caption }}"
                                        class="h-full w-full object-cover">
                                @elseif($media->thumbnail_url)
                                    <img src="{{ $media->thumbnail_url }}" alt="{{ $media->caption }}"
                                        class="h-full w-full object-cover">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/25">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90">
                                            <svg class="ml-0.5 h-4 w-4 text-gray-800" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <svg class="h-8 w-8 text-muted-foreground" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                @endif
                                @if($media->is_primary)
                                    <div class="absolute left-1.5 top-1.5">
                                        <span class="inline-flex items-center rounded-full bg-yellow-400 px-1.5 py-0.5 text-xs font-semibold text-yellow-900">★</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2 space-y-1.5">
                                @if($media->caption)
                                    <p class="truncate text-xs text-muted-foreground">{{ $media->caption }}</p>
                                @endif
                                <div class="flex items-center gap-1">
                                    @if(!$media->is_primary)
                                        <form method="POST" action="{{ route('admin.businesses.media.primary', [$business, $media]) }}" class="flex-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Set as primary"
                                                class="w-full inline-flex h-6 items-center justify-center rounded border border-input bg-background text-xs text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors">
                                                Primary
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.businesses.media.delete', [$business, $media]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete"
                                            onclick="return confirm('Delete this media?')"
                                            class="inline-flex h-6 w-6 items-center justify-center rounded border border-input bg-background text-muted-foreground hover:bg-destructive hover:text-destructive-foreground transition-colors">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Danger Zone --}}
    <div class="rounded-lg border border-red-200 bg-card text-card-foreground shadow-sm">
        <div class="flex flex-col space-y-1.5 p-6 border-b border-red-200">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-red-600">Danger Zone</h3>
            <p class="text-sm text-muted-foreground">Permanently delete this business and all its media.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.businesses.destroy', $business) }}"
                onsubmit="return confirm('Delete {{ addslashes($business->name) }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md border border-red-300 bg-background text-red-600 hover:bg-red-600 hover:text-white h-10 px-4 py-2 text-sm font-medium transition-colors">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Business
                </button>
            </form>
        </div>
    </div>
</div>

@include('admin.businesses._scripts', ['existingLat' => $business->latitude, 'existingLng' => $business->longitude])
@endsection
