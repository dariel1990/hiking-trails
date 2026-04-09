@extends('layouts.admin')

@section('title', 'Media Detail')
@section('page-title', 'Media Detail')

@section('content')
@php
    $isPhoto = $media->media_type === 'photo';

    if ($mediaSource === 'trail') {
        $ownerLabel       = 'Trail';
        $ownerName        = $attachedTo?->name ?? 'Unknown';
        $ownerUrl         = $attachedTo ? route('admin.trails.show', $attachedTo) : '#';
        $deleteUrl        = route('admin.media.trail.destroy', $media);
        $sourceBadgeClass = 'bg-green-100 text-green-800';

        $previewSrc = null;
        $embedUrl   = null;
        if ($isPhoto && $media->storage_path) {
            $previewSrc = Storage::url($media->storage_path);
        } elseif ($media->video_provider === 'youtube' && $media->video_url) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $media->video_url, $m);
            if (!empty($m[1])) {
                $previewSrc = "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
                $embedUrl   = "https://www.youtube.com/embed/{$m[1]}";
            }
        } elseif ($media->video_provider === 'vimeo' && $media->video_url) {
            preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|)(\d+)/i', $media->video_url, $m);
            if (!empty($m[1])) {
                $embedUrl = "https://player.vimeo.com/video/{$m[1]}";
            }
        }
    } elseif ($mediaSource === 'facility') {
        $ownerLabel       = 'Facility';
        $ownerName        = $attachedTo?->name ?? 'Unknown';
        $ownerUrl         = $attachedTo ? route('admin.facilities.edit', $attachedTo) : '#';
        $deleteUrl        = route('admin.media.facility.destroy', $media);
        $sourceBadgeClass = 'bg-orange-100 text-orange-800';

        $previewSrc = null;
        $embedUrl   = null;
        if ($isPhoto && $media->file_path) {
            $previewSrc = asset('storage/' . $media->file_path);
        } elseif ($media->video_provider === 'youtube' && $media->url) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->url, $m);
            if (!empty($m[1])) {
                $previewSrc = "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
                $embedUrl   = "https://www.youtube.com/embed/{$m[1]}";
            }
        } elseif ($media->video_provider === 'vimeo' && $media->url) {
            preg_match('/vimeo\.com\/(\d+)/', $media->url, $m);
            if (!empty($m[1])) {
                $embedUrl = "https://player.vimeo.com/video/{$m[1]}";
            }
        }
    } else {
        $ownerLabel       = 'Business';
        $ownerName        = $attachedTo?->name ?? 'Unknown';
        $ownerUrl         = $attachedTo ? route('admin.businesses.edit', $attachedTo) : '#';
        $deleteUrl        = route('admin.media.business.destroy', $media);
        $sourceBadgeClass = 'bg-blue-100 text-blue-800';

        $previewSrc = null;
        $embedUrl   = null;
        if ($isPhoto && $media->file_path) {
            $previewSrc = asset('storage/' . $media->file_path);
        } elseif ($media->video_provider === 'youtube' && $media->url) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->url, $m);
            if (!empty($m[1])) {
                $previewSrc = "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
                $embedUrl   = "https://www.youtube.com/embed/{$m[1]}";
            }
        } elseif ($media->video_provider === 'vimeo' && $media->url) {
            preg_match('/vimeo\.com\/(\d+)/', $media->url, $m);
            if (!empty($m[1])) {
                $embedUrl = "https://player.vimeo.com/video/{$m[1]}";
            }
        }
    }
@endphp

<div class="space-y-6">

    {{-- Breadcrumb + back --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <a href="{{ route('admin.media.index') }}" class="hover:text-foreground transition-colors">Media Library</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span>{{ $isPhoto ? 'Photo' : 'Video' }} #{{ $media->id }}</span>
            </div>
            <h2 class="text-2xl font-semibold tracking-tight">
                {{ $isPhoto ? 'Photo' : 'Video' }} Detail
            </h2>
        </div>
        <a href="{{ route('admin.media.index') }}"
            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Library
        </a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Preview --}}
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden">
                <div class="flex aspect-video items-center justify-center bg-black">
                    @if($embedUrl)
                        <iframe src="{{ $embedUrl }}" class="h-full w-full"
                            frameborder="0" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    @elseif($previewSrc)
                        <img src="{{ $previewSrc }}" alt="{{ $media->caption ?? $ownerName }}"
                            class="max-h-full max-w-full object-contain">
                    @else
                        <div class="text-sm text-white/40">No preview available</div>
                    @endif
                </div>
            </div>

            {{-- Feature usage --}}
            @if($mediaSource === 'trail' && $features->isNotEmpty())
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold leading-none tracking-tight">
                            Used in {{ $features->count() }} highlight{{ $features->count() !== 1 ? 's' : '' }}
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @foreach($features as $feature)
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-lg leading-none">{{ $feature->icon }}</span>
                                <div>
                                    <span class="font-medium">{{ $feature->name }}</span>
                                    <span class="text-muted-foreground"> — {{ $feature->trail?->name }}</span>
                                </div>
                                @if($feature->pivot->is_primary)
                                    <span class="inline-flex items-center rounded-full border border-transparent bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800">
                                        ★ Primary
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Details + Actions sidebar --}}
        <div class="space-y-4">

            {{-- Metadata --}}
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="font-semibold leading-none tracking-tight">Details</h3>
                </div>
                <div class="p-6 space-y-4 text-sm">

                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Type</span>
                        <span class="inline-flex items-center rounded-full border border-transparent px-2.5 py-0.5 text-xs font-semibold
                            {{ $isPhoto ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $isPhoto ? 'Photo' : 'Video' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-muted-foreground">Source</span>
                        <span class="inline-flex items-center rounded-full border border-transparent px-2.5 py-0.5 text-xs font-semibold {{ $sourceBadgeClass }}">
                            {{ $ownerLabel }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-4">
                        <span class="shrink-0 text-muted-foreground">Attached to</span>
                        <a href="{{ $ownerUrl }}" class="text-right font-medium hover:underline">{{ $ownerName }}</a>
                    </div>

                    @if($mediaSource === 'trail')
                        @if($media->original_name)
                            <div class="flex items-start justify-between gap-4">
                                <span class="shrink-0 text-muted-foreground">File name</span>
                                <span class="truncate text-right text-xs font-medium">{{ $media->original_name }}</span>
                            </div>
                        @endif
                        @if($media->file_size)
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">File size</span>
                                <span class="font-medium">{{ $media->formatted_size }}</span>
                            </div>
                        @endif
                        @if($media->mime_type)
                            <div class="flex items-center justify-between">
                                <span class="text-muted-foreground">MIME type</span>
                                <span class="text-xs font-medium">{{ $media->mime_type }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Featured</span>
                            <span class="font-medium">{{ $media->is_featured ? '★ Yes' : 'No' }}</span>
                        </div>
                    @else
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Primary</span>
                            <span class="font-medium">{{ $media->is_primary ? '★ Yes' : 'No' }}</span>
                        </div>
                    @endif

                    @if($media->caption)
                        <div>
                            <span class="text-muted-foreground">Caption</span>
                            <p class="mt-1 rounded-md bg-muted px-3 py-2 text-sm">{{ $media->caption }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between border-t pt-4">
                        <span class="text-muted-foreground">Uploaded</span>
                        <span class="font-medium">{{ $media->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="font-semibold leading-none tracking-tight">Actions</h3>
                </div>
                <div class="p-6 space-y-2">
                    <a href="{{ $ownerUrl }}"
                        class="inline-flex w-full items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View {{ $ownerLabel }}
                    </a>

                    <form method="POST" action="{{ $deleteUrl }}"
                        onsubmit="return confirm('Permanently delete this media item? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Permanently
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
