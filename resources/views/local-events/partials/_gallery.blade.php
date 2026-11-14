{{--
    Photo and video gallery with a full-screen viewer. Expects $event, $galleryMedia
    (the event's media, first video first) and $galleryTitle, from EventPageComposer.
    Optional $layout: 'grid' (default) or 'mosaic' (first item large). A gallery with a
    video always uses the mosaic, so the video is the big card.
    Optional $heading (bool, default true), $headingClass, $sectionClass.
--}}
@php
    $layout = $layout ?? 'grid';
    $leadIsVideo = (bool) $galleryMedia->first()?->isVideo();
    if ($leadIsVideo) {
        $layout = 'mosaic';
    }
    $galleryItems = $galleryMedia->map(fn ($m) => [
        'type' => $m->isPhoto() ? 'photo' : 'video',
        'src' => $m->isPhoto() ? $m->media_url : $m->embed_url,
        'caption' => $m->caption,
    ])->values();
    $photoCount = $galleryMedia->filter->isPhoto()->count();
    $videoCount = $galleryMedia->count() - $photoCount;
    $countLabel = collect([
        $photoCount ? $photoCount.' photo'.($photoCount === 1 ? '' : 's') : null,
        $videoCount ? $videoCount.' video'.($videoCount === 1 ? '' : 's') : null,
    ])->filter()->implode(' · ');
    // Mosaic: tile sizes per item so every row is full (2 columns on phones, 4 from md).
    $mosaicCount = $galleryMedia->count();
    $mosaicSpan = function (int $i) use ($mosaicCount): string {
        if ($mosaicCount <= 2) {
            return $mosaicCount === 1 ? 'col-span-2 row-span-2 md:col-span-4' : 'col-span-2 row-span-2';
        }
        if ($i === 0) {
            return 'col-span-2 row-span-2';
        }
        $isLast = $i === $mosaicCount - 1;
        $phone = $isLast && ($mosaicCount - 1) % 2 === 1 ? 'col-span-2' : '';
        $desktop = match (true) {
            $mosaicCount === 3, $mosaicCount === 4 && $i === 1 => 'md:col-span-2',
            $isLast && $mosaicCount >= 5 && ($mosaicCount - 5) % 4 !== 0 => [1 => 'md:col-span-4', 2 => 'md:col-span-3', 3 => 'md:col-span-2'][($mosaicCount - 5) % 4],
            default => $phone ? 'md:col-span-1' : '',
        };

        return trim($phone.' '.$desktop);
    };
@endphp
<section id="gallery" class="{{ $sectionClass ?? '' }}" aria-labelledby="gallery-heading"
    x-data="eventLightbox(@js($galleryItems))"
    @keydown.window.escape="close()" @keydown.window.arrow-left="open !== null && prev()" @keydown.window.arrow-right="open !== null && next()">
    @if($heading ?? true)
        <div class="mb-5 flex items-baseline justify-between gap-4">
            <h2 id="gallery-heading" class="{{ $headingClass ?? 'font-sans text-xl font-semibold tracking-tight text-gray-900 lg:text-xl' }}">{{ $galleryTitle }}</h2>
            <p class="text-sm text-gray-500">{{ $countLabel }}</p>
        </div>
    @else
        <h2 id="gallery-heading" class="sr-only">{{ $galleryTitle }} ({{ $countLabel }})</h2>
    @endif

    <div @class([
        'grid gap-3',
        'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4' => $layout === 'grid',
        'grid-cols-2 auto-rows-[9rem] sm:auto-rows-[11rem] md:grid-cols-4 lg:auto-rows-[13rem]' => $layout === 'mosaic',
    ])>
        @foreach($galleryMedia as $media)
            <button type="button" @click="show({{ $loop->index }})"
                @class([
                    'group relative overflow-hidden rounded-xl bg-gray-200 focus:outline-none focus-visible:ring-4 focus-visible:ring-gray-300',
                    'aspect-[4/3]' => $layout === 'grid',
                    $mosaicSpan($loop->index) => $layout === 'mosaic',
                ])
                aria-label="{{ $media->isPhoto() ? 'View photo' : 'Play video' }}{{ $media->caption ? ': '.$media->caption : '' }}">
                @php
                    $isBigVideo = $loop->first && $leadIsVideo;
                    // The big card needs a sharper thumbnail than YouTube's default 480px one.
                    $thumbnailUrl = $isBigVideo && $media->video_provider === 'youtube'
                        ? str_replace('/hqdefault.jpg', '/maxresdefault.jpg', $media->thumbnail_url)
                        : $media->thumbnail_url;
                @endphp
                @if($thumbnailUrl)
                    <img src="{{ $thumbnailUrl }}" alt="{{ $media->caption ?? $event->title }}" loading="lazy"
                        @if($thumbnailUrl !== $media->thumbnail_url)
                            data-fallback="{{ $media->thumbnail_url }}"
                            onerror="if (this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; }"
                        @endif
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                @else
                    <div class="flex h-full w-full items-center justify-center bg-gray-800 text-3xl text-white/70">▶</div>
                @endif
                @if($isBigVideo)
                    <span class="absolute inset-0 flex items-center justify-center bg-black/25 transition-colors group-hover:bg-black/35">
                        <span class="event-accent-bg flex h-16 w-16 items-center justify-center rounded-full shadow-xl transition-transform duration-200 group-hover:scale-105 md:h-20 md:w-20">
                            <svg class="ml-1 h-7 w-7 md:h-8 md:w-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                    <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        Video
                    </span>
                @elseif($media->isVideo())
                    <span class="absolute inset-0 flex items-center justify-center bg-black/20 transition-colors group-hover:bg-black/30">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-md">
                            <svg class="ml-0.5 h-5 w-5 text-gray-900" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                @endif
                @if($media->caption)
                    <span class="event-caption absolute inset-x-0 bottom-0 bg-black/55 px-3 py-2 text-left font-medium text-white {{ $isBigVideo ? 'text-sm md:px-4 md:py-3' : 'text-xs' }}"><span class="line-clamp-2">{{ $media->caption }}</span></span>
                @endif
            </button>
        @endforeach
    </div>

    <div x-show="open !== null" x-cloak x-transition.opacity
        class="fixed inset-0 z-[200] flex flex-col bg-black/95"
        role="dialog" aria-modal="true" aria-label="Photo and video viewer"
        @touchstart="touchStart($event)" @touchend="touchEnd($event)">
        <div class="flex items-center justify-between px-4 py-3 text-sm text-white/80">
            <span x-text="(open ?? 0) + 1 + ' / ' + items.length"></span>
            <button type="button" @click="close()" class="rounded-full p-2 hover:bg-white/10" aria-label="Close">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 sm:px-16">
            <template x-if="current && current.type === 'photo'">
                <img :src="current.src" :alt="current.caption || ''" class="max-h-full max-w-full rounded object-contain">
            </template>
            <template x-if="current && current.type === 'video'">
                <div class="aspect-video w-full max-w-5xl">
                    <iframe :src="current.src + (current.src.includes('?') ? '&' : '?') + 'autoplay=1'" class="h-full w-full rounded" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen title="Event video"></iframe>
                </div>
            </template>
            <button type="button" x-show="items.length > 1" @click="prev()" aria-label="Previous"
                class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:left-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" x-show="items.length > 1" @click="next()" aria-label="Next"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 sm:right-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <p class="min-h-[3rem] px-4 py-3 text-center text-sm text-white/85" x-text="current?.caption || ''"></p>
    </div>
</section>

@once
@push('scripts')
<script>
    /**
     * Full-screen viewer for the event gallery: arrow buttons, keyboard
     * arrows, swipe on phones, Escape to close. Videos play in place.
     */
    function eventLightbox(items) {
        return {
            items,
            open: null,
            startX: null,
            get current() { return this.open === null ? null : this.items[this.open]; },
            show(index) { this.open = index; document.body.style.overflow = 'hidden'; },
            close() { this.open = null; document.body.style.overflow = ''; },
            next() { this.open = (this.open + 1) % this.items.length; },
            prev() { this.open = (this.open - 1 + this.items.length) % this.items.length; },
            touchStart(e) { this.startX = e.changedTouches[0].clientX; },
            touchEnd(e) {
                if (this.startX === null || this.current?.type === 'video') { return; }
                const dx = e.changedTouches[0].clientX - this.startX;
                if (Math.abs(dx) > 50) { dx < 0 ? this.next() : this.prev(); }
                this.startX = null;
            },
        };
    }
</script>
@endpush
@endonce
