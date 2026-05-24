@extends('layouts.admin')

@section('title', 'Manage Businesses')
@section('page-title', 'Businesses')

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
            <h2 class="text-2xl font-semibold tracking-tight">Businesses</h2>
            <p class="text-sm text-muted-foreground">Manage local businesses that appear on the map</p>
        </div>
        <a href="{{ route('admin.businesses.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Business
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between pb-2">
                <p class="text-sm font-medium text-muted-foreground">Total</p>
                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-2xl font-bold">{{ $businesses->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between pb-2">
                <p class="text-sm font-medium text-muted-foreground">Active</p>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $businesses->where('is_active', true)->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between pb-2">
                <p class="text-sm font-medium text-muted-foreground">Featured</p>
                <svg class="h-4 w-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-yellow-500">{{ $businesses->where('is_featured', true)->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between pb-2">
                <p class="text-sm font-medium text-muted-foreground">Types</p>
                <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-500">{{ $businesses->pluck('business_type')->unique()->count() }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        @if($businesses->isEmpty())
            <div class="p-12 text-center">
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="rounded-full bg-muted p-3">
                        <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold">No businesses yet</h3>
                        <p class="text-sm text-muted-foreground">Add local businesses to show them on the map.</p>
                    </div>
                    <a href="{{ route('admin.businesses.create') }}"
                        class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium">
                        Add First Business
                    </a>
                </div>
            </div>
        @else
            <form id="bulk-form" method="POST" action="{{ route('admin.businesses.bulk-action') }}" class="hidden">
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
                            <th class="h-12 px-6 text-left font-medium text-muted-foreground">Business</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Type</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Address</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Media</th>
                            <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($businesses as $business)
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-4">
                                    <input type="checkbox" data-id="{{ $business->id }}"
                                        class="row-checkbox h-4 w-4 rounded border-input">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted text-lg flex-shrink-0">
                                            {{ $business->icon }}
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $business->name }}</div>
                                            @if($business->tagline)
                                                <div class="text-xs text-muted-foreground truncate max-w-[200px]">{{ $business->tagline }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full border border-transparent bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $business->business_type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-muted-foreground text-xs max-w-[180px] truncate">
                                    {{ $business->address ?? '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-2">
                                        <button type="button"
                                            data-toggle-active="{{ $business->id }}"
                                            data-active="{{ $business->is_active ? '1' : '0' }}"
                                            title="Click to toggle"
                                            class="toggle-active group inline-flex items-center gap-2">
                                            <span class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors {{ $business->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $business->is_active ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                                            </span>
                                            <span class="toggle-label text-xs font-semibold {{ $business->is_active ? 'text-green-700' : 'text-gray-500' }}">
                                                {{ $business->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </button>
                                        @if($business->is_featured)
                                            <span class="inline-flex items-center rounded-full border border-transparent bg-yellow-100 px-2.5 py-0.5 text-xs font-semibold text-yellow-800 w-fit">★ Featured</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-muted-foreground">
                                    {{ $business->media_count }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('businesses.public.show', $business->slug) }}"
                                            target="_blank"
                                            title="View on site"
                                            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.businesses.edit', $business) }}"
                                            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.businesses.destroy', $business) }}"
                                            onsubmit="return confirmDelete(event, '{{ $business->name }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
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
        @endif
    </div>
</div>

<script>
function confirmDelete(event, name) {
    event.preventDefault();
    const form = event.target;
    document.getElementById('confirm-modal-title').textContent = 'Delete "' + name + '"?';
    document.getElementById('confirm-modal-message').textContent = 'This will permanently delete this business and all its media. This cannot be undone.';
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

    document.querySelectorAll('.toggle-active').forEach(btn => {
        btn.addEventListener('click', async function () {
            if (btn.disabled) { return; }
            btn.disabled = true;
            const id = btn.dataset.toggleActive;
            try {
                const res = await fetch(`/admin/businesses/${id}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) { throw new Error('Request failed'); }
                const data = await res.json();
                const active = !!data.is_active;
                btn.dataset.active = active ? '1' : '0';
                const pill = btn.querySelector('span.relative');
                const knob = pill.querySelector('span');
                const label = btn.querySelector('.toggle-label');
                pill.classList.toggle('bg-green-500', active);
                pill.classList.toggle('bg-gray-300', !active);
                knob.classList.toggle('translate-x-4', active);
                knob.classList.toggle('translate-x-0.5', !active);
                label.textContent = active ? 'Active' : 'Inactive';
                label.classList.toggle('text-green-700', active);
                label.classList.toggle('text-gray-500', !active);
            } catch (err) {
                alert('Failed to update status.');
            } finally {
                btn.disabled = false;
            }
        });
    });
});

function submitBulkAction(action) {
    const form = document.getElementById('bulk-form');
    const selected = document.querySelectorAll('.row-checkbox:checked');
    if (selected.length === 0) { return; }

    const labels = {
        delete: { title: 'Delete selected businesses?', msg: 'This will permanently delete ' + selected.length + ' business(es) and all their media. This cannot be undone.' },
        activate: { title: 'Mark ' + selected.length + ' business(es) as Active?', msg: 'They will become visible on the map.' },
        deactivate: { title: 'Mark ' + selected.length + ' business(es) as Inactive?', msg: 'They will be hidden from the map.' },
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
