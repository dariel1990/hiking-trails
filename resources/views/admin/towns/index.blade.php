@extends('layouts.admin')

@section('title', 'Manage Towns')
@section('page-title', 'Towns')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Towns</h2>
            <p class="text-sm text-muted-foreground">
                Each town gets a landing page that pulls in its own trails, lakes, tours and businesses.
            </p>
        </div>
        <a href="{{ route('admin.towns.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium transition-colors">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Town
        </a>
    </div>

    {{-- Filters. A GET form so a filtered view is a shareable URL and the
         browser's back button behaves. --}}
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-4 space-y-4">
        <form method="GET" action="{{ route('admin.towns.index') }}" class="flex flex-col sm:flex-row gap-3">
            {{-- Preserve the active tab when searching. --}}
            @if($status !== 'all')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <div class="relative flex-1">
                <label for="town-search" class="sr-only">Search towns</label>
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" id="town-search" value="{{ $search }}"
                       placeholder="Search by name, slug, tagline or province…"
                       class="flex h-10 w-full rounded-md border border-input bg-background pl-9 pr-3 py-2 text-sm">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 text-sm font-medium transition-colors">
                    Search
                </button>
                @if($search !== '' || $status !== 'all')
                    <a href="{{ route('admin.towns.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 text-sm font-medium transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- Status tabs. Counts come from the search-filtered set, so they stay
             honest while a search is active. --}}
        <div class="flex flex-wrap gap-1 border-t pt-3 -mb-1">
            @foreach([
                'all' => 'All',
                'published' => 'Published',
                'hidden' => 'Hidden',
                'empty' => 'No trails',
            ] as $key => $label)
                <a href="{{ route('admin.towns.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                       'bg-black text-white' => $status === $key,
                       'text-muted-foreground hover:bg-accent hover:text-foreground' => $status !== $key,
                   ])
                   aria-current="{{ $status === $key ? 'page' : 'false' }}">
                    {{ $label }}
                    <span @class([
                        'rounded px-1.5 text-xs font-semibold tabular-nums',
                        'bg-white/20' => $status === $key,
                        'bg-muted' => $status !== $key,
                    ])>{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border bg-card text-card-foreground shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b bg-muted/40">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-medium">Town</th>
                        <th class="px-4 py-3 font-medium">URL</th>
                        <th class="px-4 py-3 font-medium text-right">Trails</th>
                        <th class="px-4 py-3 font-medium text-right">Businesses</th>
                        <th class="px-4 py-3 font-medium text-right">Facilities</th>
                        <th class="px-4 py-3 font-medium text-right">Tours</th>
                        <th class="px-4 py-3 font-medium">Radius</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($towns as $town)
                        <tr class="hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $town->name }}</div>
                                @if($town->tagline)
                                    <div class="text-xs text-muted-foreground line-clamp-1 max-w-xs">{{ $town->tagline }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('towns.show', $town) }}" target="_blank" rel="noopener"
                                   class="text-blue-600 hover:underline">/hiking-trails/{{ $town->slug }}</a>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $town->trails_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $town->businesses_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $town->facilities_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $town->tours_count }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $town->radius_km }} km</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.towns.toggle-active', $town) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors
                                                   {{ $town->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $town->is_active ? 'Published' : 'Hidden' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.towns.edit', $town) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
                                    <form action="{{ route('admin.towns.destroy', $town) }}" method="POST"
                                          onsubmit="return confirm('Delete {{ $town->name }}? Its trails and businesses are kept but become unassigned.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">
                                {{-- "Nothing matched" and "nothing exists" need different
                                     answers: one offers a way back, the other a way to start. --}}
                                @if($search !== '' || $status !== 'all')
                                    @php
                                        // Assembled in PHP rather than with inline @if in the
                                        // prose: Blade's directive pattern begins with \B@, so an
                                        // @endif sitting straight after a word character is left
                                        // as literal text and the block never closes.
                                        $reason = $search !== ''
                                            ? 'Nothing found for “'.$search.'”'.($status !== 'all' ? ' in this status' : '').'.'
                                            : 'No towns in this status.';
                                    @endphp
                                    <p class="font-medium text-foreground">No towns match this filter</p>
                                    <p class="mt-1 text-sm">{{ $reason }}</p>
                                    <a href="{{ route('admin.towns.index') }}"
                                       class="mt-4 inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-9 px-4 text-sm font-medium transition-colors">
                                        Clear filters
                                    </a>
                                @else
                                    No towns yet. Run <code>php artisan db:seed --class=TownSeeder</code> or add one above.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
        <p class="font-semibold mb-1">Assigning content to towns</p>
        <p>
            Each trail, business, facility and tour has a Town field on its own edit page. To fill in
            everything at once by proximity, run <code>php artisan towns:assign</code> &mdash; add
            <code>--dry-run</code> first to preview, or <code>--force</code> to redo existing assignments.
            Scope it to one town with <code>--town=houston-bc</code>; matching still runs against every
            town, so a scoped run can never take a trail that belongs to a neighbour.
        </p>
    </div>
</div>
@endsection
