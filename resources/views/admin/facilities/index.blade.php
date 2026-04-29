@extends('layouts.admin')

@section('title', 'Manage Facilities')
@section('page-title', 'Facilities')

@section('content')

{{-- Confirmation Modal --}}
<div id="confirm-modal" class="hidden fixed inset-0 bg-black/50 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-gray-200">
        <div class="p-6 space-y-4">
            <div class="space-y-2">
                <h3 id="confirm-modal-title" class="text-lg font-semibold tracking-tight text-gray-900">Are you absolutely sure?</h3>
                <p id="confirm-modal-message" class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeConfirmModal()"
                        class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-modal-action"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<div class="px-4 lg:px-8 py-6 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <span class="text-gray-700 font-medium">Facilities</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Facilities</h1>
                    <p class="text-sm text-gray-500">Manage points of interest displayed on the map</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.facilities.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium text-sm transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Facility
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 md:grid-cols-4">
        @php
            $totalCount    = $facilities->count();
            $activeCount   = $facilities->where('is_active', true)->count();
            $withMediaCount = $facilities->where('media_count', '>', 0)->count();
            $inactiveCount = $facilities->where('is_active', false)->count();
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</p>
                <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $totalCount }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Active</p>
                <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-green-600">{{ $activeCount }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">With Media</p>
                <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-blue-600">{{ $withMediaCount }}</div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Inactive</p>
                <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-400">{{ $inactiveCount }}</div>
        </div>
    </div>

    {{-- Facilities Grid (filtered by type) --}}
    @php
        $byType = $facilities->groupBy('facility_type');
        $facilityTypeMap = App\Models\Facility::getFacilityTypes();
    @endphp

    <div x-data="{ activeType: 'all' }" class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">All Facilities</h2>
            <span class="text-xs text-gray-500">
                <span x-show="activeType === 'all'">{{ $totalCount }} {{ Str::plural('facility', $totalCount) }}</span>
                @foreach($byType as $type => $items)
                    <span x-show="activeType === '{{ $type }}'" x-cloak>{{ $items->count() }} {{ Str::plural('facility', $items->count()) }}</span>
                @endforeach
            </span>
        </div>

        @if($totalCount > 0)
            {{-- Type Tabs --}}
            <div class="px-6 pt-5 pb-1 flex flex-wrap gap-2 border-b border-gray-100">
                <button type="button" x-on:click="activeType = 'all'"
                        :class="activeType === 'all' ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border text-xs font-semibold transition-colors mb-4">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>All</span>
                    <span class="opacity-75">{{ $totalCount }}</span>
                </button>

                @foreach($byType as $type => $items)
                    @php
                        $label = $facilityTypeMap[$type] ?? $type;
                        $parts = explode(' ', $label, 2);
                        $emoji = $parts[0] ?? '📍';
                        $name = $parts[1] ?? $label;
                    @endphp
                    <button type="button" x-on:click="activeType = '{{ $type }}'"
                            :class="activeType === '{{ $type }}' ? 'bg-green-600 text-white border-green-600 shadow-sm' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border text-xs font-semibold transition-colors mb-4">
                        <span class="text-sm leading-none">{{ $emoji }}</span>
                        <span>{{ $name }}</span>
                        <span class="opacity-75">{{ $items->count() }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="p-6">
            @if($totalCount > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($facilities as $facility)
                    <div x-show="activeType === 'all' || activeType === '{{ $facility->facility_type }}'"
                         data-facility-type="{{ $facility->facility_type }}"
                         class="group relative rounded-xl border border-gray-200 bg-white p-5 hover:border-gray-300 hover:shadow-md transition-all">
                        {{-- Status badge (top-right) --}}
                        @if(!$facility->is_active)
                            <span class="absolute top-4 right-4 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-600 border border-gray-200">
                                Inactive
                            </span>
                        @else
                            <span class="absolute top-4 right-4 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-green-50 text-green-700 border border-green-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                Active
                            </span>
                        @endif

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3 pr-16">
                            <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 flex items-center justify-center text-2xl">
                                {{ $facility->icon }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900 leading-tight truncate">{{ $facility->name }}</h3>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $facility->facility_type_label }}</p>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($facility->description)
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3 leading-relaxed">
                            {{ $facility->description }}
                        </p>
                        @endif

                        {{-- Meta row --}}
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 mb-4">
                            <div class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="font-mono">{{ number_format($facility->latitude, 5) }}, {{ number_format($facility->longitude, 5) }}</span>
                            </div>

                            @if($facility->media_count > 0)
                            <div class="inline-flex items-center gap-1.5 text-xs text-blue-600 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $facility->media_count }} {{ Str::plural('media', $facility->media_count) }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <a href="{{ route('admin.facilities.edit', $facility) }}"
                               class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
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
                                        class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded-md text-xs font-medium transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No facilities yet</h3>
                    <p class="text-sm text-gray-500 mb-6 max-w-sm">
                        Get started by adding facilities like parking areas, viewpoints, restrooms, and more.
                    </p>
                    <a href="{{ route('admin.facilities.create') }}"
                       class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium text-sm transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add First Facility
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Facility Types Reference --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">Available Facility Types</h2>
            <p class="text-xs text-gray-500 mt-0.5">Reference list of facility types you can create</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach(App\Models\Facility::getFacilityTypes() as $type => $label)
                @php
                    $parts = explode(' ', $label, 2);
                    $emoji = $parts[0] ?? '📍';
                    $name  = $parts[1] ?? $label;
                @endphp
                <div class="flex items-center gap-2.5 p-3 rounded-lg bg-gray-50 border border-gray-200">
                    <span class="text-xl">{{ $emoji }}</span>
                    <span class="text-sm text-gray-700 font-medium truncate">{{ $name }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
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
        function() { document.getElementById('delete-form-' + facilityId).submit(); }
    );
}

document.getElementById('confirm-modal-action').addEventListener('click', function () {
    if (confirmCallback) { confirmCallback(); }
    closeConfirmModal();
});

document.getElementById('confirm-modal').addEventListener('click', function (e) {
    if (e.target === this) { closeConfirmModal(); }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeConfirmModal(); }
});
</script>

@endsection
