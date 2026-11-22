{{--
    Overview tab media preview: the featured photo and first video side by side,
    followed by a row of thumbnails. Indexes point back into $generalMedia so
    clicks reuse the existing 'trail-general' carousel list (and its Pro video gating).
--}}
@php
    $overviewItems = collect($generalMedia ?? [])
        ->map(fn ($media, $index) => ['media' => $media, 'index' => $index])
        ->filter(fn ($item) => in_array($item['media']->media_type, ['photo', 'video_url', 'video'], true))
        ->values();

    $overviewLead = $overviewItems->first(fn ($item) => $item['media']->media_type === 'photo' && $item['media']->is_featured)
        ?? $overviewItems->first(fn ($item) => $item['media']->media_type === 'photo')
        ?? $overviewItems->first();

    $overviewLayout = $trail->overview_media_layout ?? 'gallery';
    $overviewFeaturedOnly = $overviewLayout === 'featured_only';
    $overviewShowThumbnails = $overviewLayout === 'gallery';

    $overviewSideVideo = $overviewLead && ! $overviewFeaturedOnly
        ? $overviewItems->first(fn ($item) => $item['index'] !== $overviewLead['index'] && $item['media']->media_type !== 'photo')
        : null;

    $overviewHeroItems = collect([$overviewLead, $overviewSideVideo])->filter()->values();
    $overviewHeroIndexes = $overviewHeroItems->pluck('index')->all();

    $overviewRest = ! $overviewShowThumbnails ? collect() : $overviewItems
        ->reject(fn ($item) => in_array($item['index'], $overviewHeroIndexes, true))
        ->take(4)
        ->values();

    $overviewTotal = $overviewItems->count();
    $overviewShown = $overviewHeroItems->count() + $overviewRest->count();

    $overviewThumb = function ($media): ?string {
        if ($media->media_type === 'photo') {
            return asset('storage/'.$media->storage_path);
        }

        $thumb = $media->thumbnail_url;

        return str_contains($thumb, 'video-placeholder') ? null : $thumb;
    };
@endphp

@if($overviewLead)
<div class="not-prose my-6" data-overview-media>
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-lg font-bold text-gray-900">Photos &amp; Videos</h3>
        @if($overviewTotal > 1)
            <button type="button"
                    onclick="document.querySelector('.tab-button[data-tab=photos]')?.click(); document.querySelector('.tab-nav')?.scrollIntoView({ behavior: 'smooth' });"
                    class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 hover:underline">
                View all {{ $overviewTotal }}
            </button>
        @endif
    </div>

    <div class="flex flex-col gap-2">
        <div class="grid grid-cols-1 {{ $overviewHeroItems->count() > 1 ? 'md:grid-cols-2' : '' }} gap-2">
            @foreach($overviewHeroItems as $item)
                @include('trails.partials.overview-media-tile', ['item' => $item, 'large' => true, 'moreCount' => 0])
            @endforeach
        </div>

        @if($overviewRest->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                @foreach($overviewRest as $item)
                    @include('trails.partials.overview-media-tile', [
                        'item' => $item,
                        'large' => false,
                        'moreCount' => $loop->last ? $overviewTotal - $overviewShown : 0,
                    ])
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif
