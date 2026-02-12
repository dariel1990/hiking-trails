@extends('layouts.admin')

@section('title', 'Manage Facilities')
@section('page-title', 'Facilities')

@section('content')

<!-- Confirmation Modal -->
<div id="confirm-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full transform transition-all border">
        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <h3 id="confirm-modal-title" class="text-lg font-semibold leading-none tracking-tight">Are you absolutely sure?</h3>
                <p id="confirm-modal-message" class="text-sm text-muted-foreground">This action cannot be undone. This will permanently delete the facility from our servers.</p>
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
    <!-- Header Section -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Facilities</h2>
            <p class="text-sm text-muted-foreground">
                Manage facilities and points of interest that appear on the map
            </p>
        </div>
        <a href="{{ route('admin.facilities.create') }}"
           class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Facility
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Total Facilities</p>
                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="text-2xl font-bold">{{ $facilities->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Active</p>
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $facilities->where('is_active', true)->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">With Media</p>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $facilities->where('media_count', '>', 0)->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Inactive</p>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-muted-foreground">{{ $facilities->where('is_active', false)->count() }}</div>
        </div>
    </div>

    <!-- Facilities Grid -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            @if($facilities->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($facilities as $facility)
                    <div class="group relative rounded-lg border bg-card p-4 hover:shadow-md transition-all">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted text-2xl">
                                    {{ $facility->icon }}
                                </div>
                                <div>
                                    <h3 class="font-semibold leading-tight">{{ $facility->name }}</h3>
                                    <p class="text-xs text-muted-foreground">{{ $facility->facility_type_label }}</p>
                                </div>
                            </div>
                            @if(!$facility->is_active)
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        @if($facility->description)
                        <p class="text-sm text-muted-foreground mb-3 line-clamp-2">
                            {{ $facility->description }}
                        </p>
                        @endif

                        <!-- Coordinates -->
                        <div class="flex items-center gap-2 text-xs text-muted-foreground mb-3">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-mono">{{ number_format($facility->latitude, 5) }}, {{ number_format($facility->longitude, 5) }}</span>
                        </div>

                        <!-- Media Count -->
                        @if($facility->media_count > 0)
                        <div class="flex items-center gap-2 text-xs text-blue-600 mb-3">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ $facility->media_count }} {{ Str::plural('media item', $facility->media_count) }}</span>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-2 pt-3 border-t">
                            <a href="{{ route('admin.facilities.edit', $facility) }}" 
                               class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 px-3">
                                Edit
                            </a>
                            <form action="{{ route('admin.facilities.destroy', $facility) }}" 
                                  method="POST" 
                                  id="delete-form-{{ $facility->id }}"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        onclick="confirmDelete('{{ $facility->name }}', {{ $facility->id }})"
                                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 text-white hover:bg-red-700 h-8 px-3">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="rounded-full bg-muted p-4 mb-4">
                        <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No facilities yet</h3>
                    <p class="text-sm text-muted-foreground mb-4 max-w-sm">
                        Get started by adding facilities like parking areas, viewpoints, restrooms, and more.
                    </p>
                    <a href="{{ route('admin.facilities.create') }}" 
                       class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add First Facility
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Facility Types Reference -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Available Facility Types</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                @foreach(App\Models\Facility::getFacilityTypes() as $type => $label)
                <div class="flex items-center gap-2 p-2 rounded-md bg-muted/50">
                    <span class="text-lg">{{ explode(' ', $label)[0] }}</span>
                    <span class="text-muted-foreground">{{ explode(' ', $label)[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
// Modal functions
let confirmCallback = null;

function showConfirmModal(title, message, callback) {
    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-message').textContent = message;
    confirmCallback = callback;
    document.getElementById('confirm-modal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    confirmCallback = null;
}

function confirmDelete(facilityName, facilityId) {
    showConfirmModal(
        'Delete Facility',
        `Are you sure you want to delete "${facilityName}"? This action cannot be undone.`,
        function() {
            document.getElementById('delete-form-' + facilityId).submit();
        }
    );
}

document.getElementById('confirm-modal-action').addEventListener('click', function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
});

// Close modal on backdrop click
document.getElementById('confirm-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeConfirmModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmModal();
    }
});
</script>

@endsection