{{--
    Public-facing carousel of approved community photos for a trail.

    Required vars in scope:
        $trail — Trail model.
--}}
@php
    $emptyStateLabel = $trail->isFishingLake() ? 'this fishing lake' : 'this trail';
@endphp

<div
    x-data="trailPhotoCarousel({ trailId: {{ (int) $trail->id }}, endpoint: '{{ url('/api/trail-photos') }}' })"
    x-init="load()"
    class="mb-6"
>
    <div class="flex items-end justify-between mb-3">
        <h3 class="text-lg font-semibold text-gray-900">Community Photos</h3>
        <span x-show="photos.length > 0" x-cloak class="text-sm text-gray-500">
            <span x-text="active + 1"></span> of <span x-text="photos.length"></span>
        </span>
    </div>

    {{-- Loading state --}}
    <template x-if="loading">
        <div class="aspect-video bg-gray-100 rounded-lg animate-pulse"></div>
    </template>

    {{-- Empty state --}}
    <template x-if="!loading && photos.length === 0">
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
            <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 16.5V7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5Z"/>
                <circle cx="12" cy="12" r="3.5" stroke-width="2"/>
            </svg>
            <p class="text-gray-700 font-medium">Be the first to share a photo of {{ $emptyStateLabel }}.</p>
            <p class="text-sm text-gray-500 mt-1">Use the "Submit a photo" button below to contribute.</p>
        </div>
    </template>

    {{-- Carousel --}}
    <template x-if="!loading && photos.length > 0">
        <div class="relative rounded-lg overflow-hidden bg-black">
            <div class="aspect-video relative">
                <template x-for="(photo, index) in photos" :key="photo.id">
                    <div
                        class="absolute inset-0 transition-opacity duration-300"
                        :class="index === active ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
                    >
                        <img :src="photo.image_url" :alt="photo.caption || 'Community photo'" class="w-full h-full object-cover">
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent text-white p-4">
                            <p x-show="photo.caption" x-text="photo.caption" class="text-sm sm:text-base"></p>
                            <p class="text-xs text-white/80 mt-1">By <span x-text="photo.submitter_name"></span></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Prev / Next --}}
            <button type="button" x-show="photos.length > 1" @click="prev()"
                aria-label="Previous photo"
                class="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-black/40 hover:bg-black/70 text-white rounded-full w-10 h-10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button type="button" x-show="photos.length > 1" @click="next()"
                aria-label="Next photo"
                class="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-black/40 hover:bg-black/70 text-white rounded-full w-10 h-10 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Dots --}}
            <div x-show="photos.length > 1" class="absolute bottom-2 left-1/2 -translate-x-1/2 z-20 flex gap-1.5">
                <template x-for="(photo, index) in photos" :key="'dot-'+photo.id">
                    <button type="button" @click="active = index"
                        :aria-label="'Go to photo ' + (index + 1)"
                        :class="index === active ? 'bg-white' : 'bg-white/40 hover:bg-white/70'"
                        class="w-2 h-2 rounded-full"></button>
                </template>
            </div>
        </div>
    </template>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('trailPhotoCarousel', (config) => ({
                    loading: true,
                    photos: [],
                    active: 0,
                    async load() {
                        try {
                            const res = await fetch(config.endpoint + '?trail_id=' + config.trailId, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!res.ok) throw new Error('Failed to load');
                            const data = await res.json();
                            this.photos = data.data || [];
                        } catch (e) {
                            this.photos = [];
                        } finally {
                            this.loading = false;
                        }
                    },
                    prev() {
                        if (this.photos.length === 0) return;
                        this.active = (this.active - 1 + this.photos.length) % this.photos.length;
                    },
                    next() {
                        if (this.photos.length === 0) return;
                        this.active = (this.active + 1) % this.photos.length;
                    },
                }));
            });
        </script>
    @endpush
@endonce
