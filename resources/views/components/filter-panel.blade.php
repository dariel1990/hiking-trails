@props([
    'action',
    'filters' => [],
    'searchLabel' => 'Search',
    'searchPlaceholder' => 'Search…',
    'submitLabel' => 'Show results',
    'resultCount' => null,
    'resultNoun' => 'result',
])

@php
    /**
     * Normalise each filter to a predictable shape and resolve its current
     * value once, so the bar, the chips and the sheet all agree.
     *
     * @var array<int, array<string, mixed>> $resolved
     */
    $resolved = collect($filters)->map(function (array $filter) {
        $options = $filter['options'] ?? [];

        return [
            'name' => $filter['name'],
            'label' => $filter['label'],
            'placeholder' => $filter['placeholder'] ?? 'Any',
            'options' => $options instanceof \Illuminate\Support\Collection ? $options->all() : $options,
            'value' => request($filter['name']),
        ];
    })->values();

    $active = $resolved->filter(fn ($filter) => filled($filter['value']));
    $searchTerm = request('search');
    $activeCount = $active->count() + (filled($searchTerm) ? 1 : 0);

    // Every param this panel owns, so callers never hand-maintain the list.
    $ownedKeys = $resolved->pluck('name')->push('search')->all();

    $sheetId = 'filter-sheet-'.Str::random(6);

    /**
     * A chip's dismiss link: the current query minus that one key, and minus
     * the pagination cursor so removing a filter returns to page one.
     */
    $withoutFilter = fn (string $name) => $action.'?'.http_build_query(
        collect(request()->query())->except([$name, 'hiking_page', 'lake_page', 'page'])->all()
    );
@endphp

<div class="w-full max-w-3xl mx-auto scale-in" style="animation-delay: 0.4s;"
     x-data="{
        open: false,
        toggle(state) {
            this.open = state;
            document.body.style.overflow = state ? 'hidden' : '';
            /* No x-trap here: @alpinejs/focus is not installed. Moving focus in
               and back out covers the common path without the dependency. */
            this.$nextTick(() => (state ? this.$refs.closeButton : this.$refs.openButton)?.focus());
        }
     }"
     @keydown.escape.window="open && toggle(false)">

    <form method="GET" action="{{ $action }}">

        {{-- Compact bar: search plus one button. Replaces a stack of selects
             that ran to roughly 550px on a phone. --}}
        <div class="flex gap-2 rounded-2xl bg-white/20 backdrop-blur-md p-2 shadow-2xl border border-white/30">
            <label for="filter-search" class="sr-only">{{ $searchLabel }}</label>
            <div class="relative flex-1 min-w-0">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input type="text" name="search" id="filter-search"
                       value="{{ $searchTerm }}"
                       placeholder="{{ $searchPlaceholder }}"
                       class="w-full h-12 pl-10 pr-3 bg-white/95 border-0 rounded-xl text-gray-900 placeholder-gray-500 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-400">
            </div>

            <button type="button"
                    x-ref="openButton"
                    @click="toggle(true)"
                    :aria-expanded="open"
                    aria-controls="{{ $sheetId }}"
                    class="filter-open-btn flex-shrink-0 inline-flex items-center gap-2 h-12 px-4 rounded-xl bg-forest-600 text-white font-semibold shadow-md transition-colors hover:bg-forest-700 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/>
                </svg>
                <span class="max-sm:sr-only">Filters</span>
                @if($active->isNotEmpty())
                    <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-accent-500 text-white text-xs font-bold tabular-nums">{{ $active->count() }}</span>
                @endif
            </button>
        </div>

        {{-- Active filters, each removable on its own. Plain links, so this
             works with JS disabled and needs no client state. --}}
        @if($active->isNotEmpty() || filled($searchTerm))
            <div class="flex flex-wrap items-center gap-2 mt-3">
                @if(filled($searchTerm))
                    <a href="{{ $withoutFilter('search') }}"
                       class="filter-chip group"
                       aria-label="Remove search term {{ $searchTerm }}">
                        <span class="opacity-70">Search:</span> {{ Str::limit($searchTerm, 24) }}
                        <span aria-hidden="true" class="filter-chip-x">&times;</span>
                    </a>
                @endif

                @foreach($active as $filter)
                    <a href="{{ $withoutFilter($filter['name']) }}"
                       class="filter-chip group"
                       aria-label="Remove {{ $filter['label'] }} filter">
                        <span class="opacity-70">{{ $filter['label'] }}:</span>
                        {{ strip_tags($filter['options'][$filter['value']] ?? $filter['value']) }}
                        <span aria-hidden="true" class="filter-chip-x">&times;</span>
                    </a>
                @endforeach

                @if($activeCount > 1)
                    <a href="{{ $action }}" class="text-xs font-semibold text-white/80 hover:text-white underline underline-offset-2 px-1">
                        Clear all
                    </a>
                @endif
            </div>
        @endif

        @if($resultCount !== null && $activeCount > 0)
            <p class="mt-3 text-sm font-medium text-white/90">
                {{ number_format($resultCount) }} {{ Str::plural($resultNoun, $resultCount) }} found
            </p>
        @endif

        {{-- The sheet lives inside the form, so its selects submit with the
             search field above. Fixed positioning does not break that: form
             association follows the DOM tree, not the layout. --}}
        <div id="{{ $sheetId }}"
             class="filter-sheet"
             :class="open && 'is-open'"
             x-show="open"
             x-cloak
             role="dialog"
             aria-modal="true"
             aria-labelledby="{{ $sheetId }}-title">

            <div class="filter-sheet-backdrop" @click="toggle(false)"></div>

            <div class="filter-sheet-dialog" @click.outside="open && toggle(false)">
                <header class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-100 flex-shrink-0">
                    <h2 id="{{ $sheetId }}-title" class="text-lg font-bold text-gray-900">Filters</h2>
                    <button type="button"
                            x-ref="closeButton"
                            @click="toggle(false)"
                            class="filter-sheet-close"
                            aria-label="Close filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    @foreach($resolved as $filter)
                        <div>
                            <label for="filter-{{ $filter['name'] }}" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ $filter['label'] }}
                            </label>
                            <select name="{{ $filter['name'] }}" id="filter-{{ $filter['name'] }}" class="filter-sheet-select">
                                <option value="">{{ $filter['placeholder'] }}</option>
                                @foreach($filter['options'] as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" @selected((string) $filter['value'] === (string) $optionValue)>
                                        {{ $optionLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <footer class="flex gap-3 px-5 py-4 border-t border-gray-100 flex-shrink-0">
                    <a href="{{ $action }}" class="filter-sheet-clear">Clear all</a>
                    <button type="submit" class="filter-sheet-apply">{{ $submitLabel }}</button>
                </footer>
            </div>
        </div>
    </form>
</div>
