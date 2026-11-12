@php($rowMap = $town->hero_image ? asset('storage/'.$town->hero_image) : $town->staticMapUrl(200, 240))
<article class="flex-1">
    <a href="{{ route('towns.show', $town) }}"
       class="town-row group grid grid-cols-[6.5rem,1fr,auto] sm:grid-cols-[7.5rem,1fr,auto] items-stretch h-full rounded-2xl overflow-hidden bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">

        <div class="relative overflow-hidden bg-forest-800">
            @if($rowMap)
                <img src="{{ $rowMap }}"
                     alt="Terrain around {{ $town->name }}, British Columbia"
                     loading="lazy"
                     class="town-terrain absolute inset-0 w-full h-full object-cover opacity-90">
            @endif
            <div class="absolute inset-0 bg-forest-900/25"></div>
            <div class="absolute inset-0 bg-pattern-contour opacity-25 mix-blend-overlay"></div>
        </div>

        <div class="min-w-0 py-5 px-5">
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-forest-700 transition-colors leading-tight">
                {{ $town->name }}<span class="province-tag align-middle ml-2 text-forest-400">{{ $town->province_code }}</span>
            </h3>

            @if($town->tagline)
                <p class="text-sm text-gray-500 mt-1.5 line-clamp-2 leading-snug text-pretty">{{ $town->tagline }}</p>
            @endif

            <p class="mt-3 flex items-center gap-2 text-xs text-gray-500 tabular-nums">
                <span><span class="font-bold text-forest-700">{{ $town->hiking_trails_count }}</span> {{ Str::plural('trail', $town->hiking_trails_count) }}</span>
                @if($town->fishing_lakes_count)
                    <span class="w-1 h-1 rounded-full bg-gray-300" aria-hidden="true"></span>
                    <span><span class="font-bold text-forest-700">{{ $town->fishing_lakes_count }}</span> {{ Str::plural('lake', $town->fishing_lakes_count) }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center pr-5 pl-1 text-gray-300 group-hover:text-accent-500">
            <svg class="town-row-arrow w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
</article>
