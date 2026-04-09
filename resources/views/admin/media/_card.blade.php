@php
    $isPhoto    = $media->media_type === 'photo';
    $isFeatured = isset($media->is_featured) && $media->is_featured;
    $isPrimary  = isset($media->is_primary)  && $media->is_primary;

    if ($mediaSource === 'trail') {
        $detailUrl        = route('admin.media.trail.show',    $media);
        $deleteUrl        = route('admin.media.trail.destroy', $media);
        $ownerName        = $media->trail?->name ?? 'Unknown trail';
        $ownerUrl         = $media->trail ? route('admin.trails.show', $media->trail) : '#';
        $sourceBadgeClass = 'bg-green-100 text-green-800';
        $sourceBadgeLabel = 'Trail';
        $thumbSrc         = $isPhoto && $media->storage_path ? Storage::url($media->storage_path) : null;
        if (!$thumbSrc && $media->video_provider === 'youtube' && $media->video_url) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $media->video_url, $yt);
            $thumbSrc = !empty($yt[1]) ? "https://img.youtube.com/vi/{$yt[1]}/mqdefault.jpg" : null;
        }
    } elseif ($mediaSource === 'facility') {
        $detailUrl        = route('admin.media.facility.show',    $media);
        $deleteUrl        = route('admin.media.facility.destroy', $media);
        $ownerName        = $media->facility?->name ?? 'Unknown facility';
        $ownerUrl         = $media->facility ? route('admin.facilities.edit', $media->facility) : '#';
        $sourceBadgeClass = 'bg-orange-100 text-orange-800';
        $sourceBadgeLabel = 'Facility';
        $thumbSrc         = $isPhoto && $media->file_path ? asset('storage/' . $media->file_path) : null;
        if (!$thumbSrc && $media->video_provider === 'youtube' && $media->url) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->url, $yt);
            $thumbSrc = !empty($yt[1]) ? "https://img.youtube.com/vi/{$yt[1]}/mqdefault.jpg" : null;
        }
    } else {
        $detailUrl        = route('admin.media.business.show',    $media);
        $deleteUrl        = route('admin.media.business.destroy', $media);
        $ownerName        = $media->business?->name ?? 'Unknown business';
        $ownerUrl         = $media->business ? route('admin.businesses.edit', $media->business) : '#';
        $sourceBadgeClass = 'bg-blue-100 text-blue-800';
        $sourceBadgeLabel = 'Business';
        $thumbSrc         = $isPhoto && $media->file_path ? asset('storage/' . $media->file_path) : null;
        if (!$thumbSrc && $media->video_provider === 'youtube' && $media->url) {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/', $media->url, $yt);
            $thumbSrc = !empty($yt[1]) ? "https://img.youtube.com/vi/{$yt[1]}/mqdefault.jpg" : null;
        }
    }
@endphp

<div class="group relative overflow-hidden rounded-lg border bg-card text-card-foreground shadow-sm transition-shadow hover:shadow-md">

    {{-- Thumbnail --}}
    <a href="{{ $detailUrl }}" class="relative block aspect-square overflow-hidden bg-muted">
        @if($thumbSrc)
            <img src="{{ $thumbSrc }}" alt="{{ $media->caption ?? $ownerName }}"
                class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105">
        @elseif(!$isPhoto)
            <div class="flex h-full w-full items-center justify-center bg-gray-900">
                <svg class="h-8 w-8 text-white/60" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        @else
            <div class="flex h-full w-full items-center justify-center bg-muted">
                <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif

        {{-- Video play overlay --}}
        @if(!$isPhoto && $thumbSrc)
            <div class="absolute inset-0 flex items-center justify-center bg-black/25">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90">
                    <svg class="ml-0.5 h-4 w-4 text-gray-800" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
            </div>
        @endif

        {{-- Star badge --}}
        @if($isFeatured || $isPrimary)
            <div class="absolute left-1.5 top-1.5">
                <span class="inline-flex items-center rounded-full border border-transparent bg-yellow-400 px-1.5 py-0.5 text-xs font-semibold text-yellow-900">★</span>
            </div>
        @endif

        {{-- Type badge --}}
        <div class="absolute right-1.5 top-1.5">
            <span class="inline-flex items-center rounded-full border border-transparent px-1.5 py-0.5 text-xs font-semibold
                {{ $isPhoto ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                {{ $isPhoto ? 'Photo' : 'Video' }}
            </span>
        </div>
    </a>

    {{-- Info row --}}
    <div class="p-3 space-y-1.5">
        <a href="{{ $ownerUrl }}"
            class="block truncate text-xs font-medium leading-tight text-foreground hover:underline">
            {{ $ownerName }}
        </a>
        @if($media->caption)
            <p class="truncate text-xs text-muted-foreground">{{ $media->caption }}</p>
        @endif
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center rounded-full border border-transparent px-2 py-0.5 text-xs font-semibold {{ $sourceBadgeClass }}">
                {{ $sourceBadgeLabel }}
            </span>
            <form method="POST" action="{{ $deleteUrl }}"
                onsubmit="return confirm('Delete this media item? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" title="Delete"
                    class="inline-flex h-6 w-6 items-center justify-center rounded-md border border-input bg-background text-muted-foreground transition-colors hover:bg-destructive hover:text-destructive-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
