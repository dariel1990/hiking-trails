@php
    $media = $item['media'];
    $isVideo = $media->media_type !== 'photo';
    $thumb = $overviewThumb($media);
@endphp

<button type="button"
        onclick="openMediaCarousel('trail-general', {{ $item['index'] }})"
        aria-label="{{ $isVideo ? 'Play video' : 'View photo' }}: {{ $media->caption ?? $trail->name }}"
        class="relative group overflow-hidden bg-gray-900 {{ $large ? 'w-full aspect-video rounded-xl shadow-md' : 'aspect-square rounded-lg' }}">
    @if($thumb)
        <img src="{{ $thumb }}"
             alt="{{ $media->caption ?? $trail->name }}"
             loading="{{ $large ? 'eager' : 'lazy' }}"
             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
    @else
        <div class="w-full h-full flex items-center justify-center">
            <svg class="w-10 h-10 text-white opacity-75" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
            </svg>
        </div>
    @endif

    @if($isVideo)
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="bg-white/90 rounded-full {{ $large ? 'p-4' : 'p-2' }} shadow-lg">
                <svg class="{{ $large ? 'w-8 h-8' : 'w-5 h-5' }} text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                </svg>
            </div>
        </div>
        <span class="absolute top-2 left-2 bg-red-600 text-white px-2 py-0.5 rounded text-xs font-medium">Video</span>
    @elseif($large && $media->is_featured)
        <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-medium">⭐ Featured</span>
    @endif

    @if($large && $media->caption)
        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent text-white text-left px-4 py-3">
            <p class="text-sm">{{ $media->caption }}</p>
        </div>
    @endif

    @if($moreCount > 0)
        <div class="absolute inset-0 bg-black/50 flex items-center justify-center text-white text-lg font-bold pointer-events-none">
            +{{ $moreCount }}
        </div>
    @endif
</button>
