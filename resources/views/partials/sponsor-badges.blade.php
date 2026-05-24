{{--
    Floating sponsor badges for a trail network (stacked vertically in the bottom-right).
    Required: $network (TrailNetwork).
--}}
@php
    $activeSponsors = $network?->activeSponsors ?? collect();
@endphp

@if($activeSponsors->count() > 0)
    <div class="fixed bottom-3 md:bottom-3 right-12 md:right-12 z-[45] flex flex-col gap-2 items-end">
        @foreach($activeSponsors as $sponsor)
            @php
                $hasUrl = ! empty($sponsor->url);
                $tag = $hasUrl ? 'a' : 'div';
                $linkAttrs = $hasUrl ? 'href="'.e($sponsor->url).'" target="_blank" rel="noopener noreferrer"' : '';
            @endphp
            <{!! $tag !!} {!! $linkAttrs !!} class="block">
                <div class="bg-white rounded-lg shadow-xl border-2 border-accent-500/20 p-3 md:p-4 hover:shadow-2xl hover:scale-105 transition-all duration-300 group">
                    <div class="flex items-center space-x-2 md:space-x-3">
                        <div class="flex-shrink-0">
                            <img src="{{ $sponsor->logoUrl() }}"
                                 alt="{{ $sponsor->name }} logo"
                                 class="w-10 h-10 md:w-12 md:h-12 object-contain group-hover:scale-110 transition-transform">
                        </div>
                        <div class="text-left">
                            <p class="text-xs text-gray-500 font-medium">Sponsored by</p>
                            <p class="text-sm font-bold text-gray-900 group-hover:text-accent-600 transition-colors">{{ $sponsor->name }}</p>
                            @if($sponsor->tagline)
                                <p class="text-xs text-forest-600 font-semibold">{{ $sponsor->tagline }}</p>
                            @endif
                        </div>
                    </div>
                    @if($hasUrl)
                        <div class="mt-2 pt-2 border-t border-gray-100 hidden md:block">
                            <p class="text-xs text-gray-500 group-hover:text-accent-600 transition-colors flex items-center space-x-1">
                                <span>Learn more</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </p>
                        </div>
                    @endif
                </div>
            </{!! $tag !!}>
        @endforeach
    </div>
@endif
