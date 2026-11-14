@extends('layouts.admin')

@section('title', 'Manage Events')
@section('page-title', 'Events')

@section('content')

<!-- Confirmation Modal -->
<div id="confirm-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full transform transition-all border">
        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <h3 id="confirm-modal-title" class="text-lg font-semibold leading-none tracking-tight">Are you absolutely sure?</h3>
                <p id="confirm-modal-message" class="text-sm text-muted-foreground">This action cannot be undone.</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeConfirmModal()" class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 text-sm font-medium transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-modal-action" class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-9 px-4 py-2 text-sm font-medium transition-colors">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Events</h2>
            <p class="text-sm text-muted-foreground">Events appear on the Events page (with their map) and on the events calendar at xploresmithers.com.</p>
        </div>
        <a href="{{ route('admin.local-events.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Event
        </a>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.local-events.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search title, venue or location..."
            class="flex h-10 w-full sm:max-w-xs rounded-md border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
        <select name="when" onchange="this.form.submit()"
            class="h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
            <option value="upcoming" @selected($when === 'upcoming')>Upcoming &amp; ongoing</option>
            <option value="past" @selected($when === 'past')>Past</option>
            <option value="all" @selected($when === 'all')>All dates</option>
        </select>
        <select name="source" onchange="this.form.submit()"
            class="h-10 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring">
            <option value="" @selected(! $source)>All events</option>
            <option value="manual" @selected($source === 'manual')>Added here</option>
            <option value="imported" @selected($source === 'imported')>Imported (SmithersEvents.com)</option>
        </select>
        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 text-sm font-medium">Search</button>
    </form>

    <!-- Table -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        @if($events->isEmpty())
            <div class="p-12 text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="rounded-full bg-muted p-3">
                        <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold">No events found</h3>
                        <p class="text-sm text-muted-foreground">Try another filter, or add a new event.</p>
                    </div>
                    <a href="{{ route('admin.local-events.create') }}"
                        class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium">
                        Add Event
                    </a>
                </div>
            </div>
        @else
            <form id="bulk-form" method="POST" action="{{ route('admin.local-events.bulk-action') }}" class="hidden">
                @csrf
                <input type="hidden" name="action" id="bulk-action-input" value="">
                <div id="bulk-ids-container"></div>
            </form>
            <div id="bulk-action-bar" style="display: none;" class="items-center justify-between gap-4 px-6 py-3 border-b bg-blue-50">
                <div class="text-sm font-medium text-blue-900">
                    <span id="bulk-selected-count">0</span> selected
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="submitBulkAction('activate')"
                        class="inline-flex items-center justify-center rounded-md border border-input bg-white hover:bg-green-50 hover:text-green-700 h-9 px-3 text-sm font-medium transition-colors">
                        Mark Active
                    </button>
                    <button type="button" onclick="submitBulkAction('deactivate')"
                        class="inline-flex items-center justify-center rounded-md border border-input bg-white hover:bg-gray-100 h-9 px-3 text-sm font-medium transition-colors">
                        Mark Inactive
                    </button>
                    <button type="button" onclick="submitBulkAction('delete')"
                        class="inline-flex items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700 h-9 px-3 text-sm font-medium transition-colors">
                        Delete
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/50">
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground w-10">
                                <input type="checkbox" id="select-all" class="h-4 w-4 rounded border-input">
                            </th>
                            <th class="h-12 px-6 text-left font-medium text-muted-foreground">Event</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Date</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Map</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($events as $event)
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-4">
                                    <input type="checkbox" data-id="{{ $event->id }}"
                                        class="row-checkbox h-4 w-4 rounded border-input">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($event->cover_image_url)
                                            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}"
                                                class="h-10 w-10 rounded object-cover flex-shrink-0">
                                        @else
                                            <div class="flex h-10 w-10 items-center justify-center rounded bg-muted text-lg flex-shrink-0">{{ $event->marker_icon }}</div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-medium">{{ $event->title }}</div>
                                            <div class="text-xs text-muted-foreground truncate max-w-[260px]">
                                                {{ $event->venue ?: $event->location }}
                                                @if(! $event->isManual())
                                                    <span class="ml-1 inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600">Imported</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div>{{ $event->event_date->format('M j, Y') }}@if($event->is_multi_day) – {{ $event->end_date->format('M j') }}@endif</div>
                                    @if($event->time_range_label)
                                        <div class="text-xs text-muted-foreground">{{ $event->time_range_label }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-muted-foreground whitespace-nowrap">
                                    @if($event->route_coordinates)
                                        Route ·
                                    @endif
                                    {{ $event->stops_count }} activit{{ $event->stops_count === 1 ? 'y' : 'ies' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-1.5">
                                        @if($event->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 w-fit">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 w-fit">Inactive</span>
                                        @endif
                                        @if($event->is_featured)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 w-fit">★ Featured</span>
                                        @endif
                                        @if($event->isChristmasEvent())
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800 w-fit">🎄 Christmas</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('local-events.show', $event->slug) }}"
                                            target="_blank"
                                            title="View on site"
                                            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.local-events.edit', $event) }}" title="Edit"
                                            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.local-events.destroy', $event) }}"
                                            onsubmit="return confirmDelete(event, @js($event->title))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete"
                                                class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-8 w-8 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
                <div class="border-t px-6 py-4">
                    {{ $events->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<script>
const sectionNoun = 'event';
const sectionNounPlural = 'events';

function confirmDelete(event, name) {
    event.preventDefault();
    const form = event.target;
    document.getElementById('confirm-modal-title').textContent = 'Delete "' + name + '"?';
    document.getElementById('confirm-modal-message').textContent = 'This will permanently delete this event and its map activities. This cannot be undone.';
    document.getElementById('confirm-modal-action').onclick = () => form.submit();
    document.getElementById('confirm-modal').classList.remove('hidden');
    return false;
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bar = document.getElementById('bulk-action-bar');
    const countEl = document.getElementById('bulk-selected-count');

    function updateBar() {
        const selected = document.querySelectorAll('.row-checkbox:checked');
        countEl.textContent = selected.length;
        bar.style.display = selected.length === 0 ? 'none' : 'flex';
        if (selectAll) {
            selectAll.checked = selected.length > 0 && selected.length === rowCheckboxes.length;
            selectAll.indeterminate = selected.length > 0 && selected.length < rowCheckboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => { cb.checked = selectAll.checked; });
            updateBar();
        });
    }
    rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBar));
});

function submitBulkAction(action) {
    const form = document.getElementById('bulk-form');
    const selected = document.querySelectorAll('.row-checkbox:checked');
    if (selected.length === 0) { return; }

    const labels = {
        delete: { title: 'Delete selected ' + sectionNounPlural + '?', msg: 'This will permanently delete ' + selected.length + ' ' + sectionNoun + '(s). This cannot be undone.' },
        activate: { title: 'Mark ' + selected.length + ' ' + sectionNoun + '(s) as Active?', msg: 'They will become visible on the site.' },
        deactivate: { title: 'Mark ' + selected.length + ' ' + sectionNoun + '(s) as Inactive?', msg: 'They will be hidden from the site.' },
    };
    const cfg = labels[action];

    document.getElementById('confirm-modal-title').textContent = cfg.title;
    document.getElementById('confirm-modal-message').textContent = cfg.msg;
    document.getElementById('confirm-modal-action').onclick = () => {
        document.getElementById('bulk-action-input').value = action;
        const container = document.getElementById('bulk-ids-container');
        container.innerHTML = '';
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.dataset.id;
            container.appendChild(input);
        });
        form.submit();
    };
    document.getElementById('confirm-modal').classList.remove('hidden');
}
</script>
@endsection
