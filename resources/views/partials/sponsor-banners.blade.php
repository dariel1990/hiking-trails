{{--
    Sponsor banners (top + floating badge) for a trail network.
    Required: $network (TrailNetwork). Renders nothing when no active sponsors.
--}}
@php
    $activeSponsors = $network?->activeSponsors ?? collect();
@endphp

@if($activeSponsors->count() > 0)
    {{-- Top sponsor banner stack --}}
    <div class="fixed top-20 left-0 right-0 z-[60] space-y-1">
        @foreach($activeSponsors as $sponsor)
            @php
                $bannerId = 'sponsor-banner-'.$sponsor->id;
                $hasUrl = ! empty($sponsor->url);
                $tag = $hasUrl ? 'a' : 'div';
                $linkAttrs = $hasUrl ? 'href="'.e($sponsor->url).'" target="_blank" rel="noopener noreferrer"' : '';
            @endphp
            <div id="{{ $bannerId }}" class="bg-gradient-to-r from-accent-500 to-forest-600 shadow-lg" data-sponsor-banner="{{ $sponsor->id }}">
                <div class="max-w-4xl mx-auto px-4 py-3">
                    <div class="flex items-center justify-between">
                        <{!! $tag !!} {!! $linkAttrs !!} class="flex items-center space-x-4 flex-1 hover:opacity-90 transition-opacity group">
                            <div class="flex-shrink-0 bg-white rounded-lg p-1.5">
                                <img src="{{ $sponsor->logoUrl() }}"
                                     alt="{{ $sponsor->name }} logo"
                                     class="w-8 h-8 object-contain group-hover:scale-110 transition-transform">
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <p class="text-white font-medium text-sm md:text-base">
                                    @if(! empty($sponsor->welcome_message))
                                        <span class="hidden md:inline">{{ $sponsor->welcome_message }} </span>
                                    @endif
                                    {{ $sponsor->banner_text ?: 'Proudly sponsored by' }}
                                    <span class="font-bold"> <br>{{ $sponsor->name }}@if($sponsor->tagline) – {{ $sponsor->tagline }}@endif</span>
                                </p>
                            </div>
                            @if(! empty($sponsor->cta_text) && $hasUrl)
                                <div class="hidden md:flex items-center space-x-2 px-4 py-2 bg-white/20 rounded-lg hover:bg-white/30 transition-colors">
                                    <span class="text-white text-sm font-semibold">{{ $sponsor->cta_text }}</span>
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </div>
                            @endif
                        </{!! $tag !!}>
                        <button type="button"
                                onclick="document.getElementById('{{ $bannerId }}').style.display='none'; try { localStorage.setItem('sponsor-banner-dismissed-{{ $sponsor->id }}', '1'); } catch(e) {}"
                                class="flex-shrink-0 ml-4 text-white hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            try {
                @foreach($activeSponsors as $sponsor)
                    if (localStorage.getItem('sponsor-banner-dismissed-{{ $sponsor->id }}') === '1') {
                        var el = document.getElementById('sponsor-banner-{{ $sponsor->id }}');
                        if (el) { el.style.display = 'none'; }
                    }
                @endforeach
            } catch (e) {}
        })();
    </script>
@endif
