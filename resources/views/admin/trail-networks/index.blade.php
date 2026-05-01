@extends('layouts.admin')

@section('title', 'Trail Networks')
@section('page-title', 'Trail Networks')

@section('content')
@php
    $typeStyles = [
        'nordic_skiing'   => 'bg-blue-100 text-blue-800',
        'downhill_skiing' => 'bg-purple-100 text-purple-800',
        'hiking'          => 'bg-green-100 text-green-800',
        'mountain_biking' => 'bg-orange-100 text-orange-800',
    ];
    $seasonStyles = [
        'summer' => ['label' => '☀️ Summer', 'class' => 'bg-amber-100 text-amber-800'],
        'winter' => ['label' => '❄️ Winter', 'class' => 'bg-sky-100 text-sky-800'],
        'both'   => ['label' => 'Both seasons', 'class' => 'bg-gray-100 text-gray-700'],
    ];

    $totalTrails = $networks->sum('trails_count');
    $alwaysVisibleCount = $networks->where('is_always_visible', true)->count();
@endphp

<div class="px-4 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 mb-6 border-b border-gray-200">
        <div>
            <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Admin</a>
                <span>/</span>
                <span class="text-gray-700 font-medium">Trail Networks</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-600 border-2 border-white shadow-md flex items-center justify-center text-lg select-none">
                    🏔️
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Trail Networks</h1>
                    <p class="text-sm text-gray-500">{{ $networks->count() }} network(s) · {{ $totalTrails }} trail(s)</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.trail-networks.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-medium text-sm transition-colors shadow-sm">
                + Add New Network
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-lg border border-green-200 bg-green-50 text-green-800">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-lg border border-red-200 bg-red-50 text-red-800">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l3.273-3.273A6 6 0 1116.727 17.727L21 21H3z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 leading-none">{{ $networks->count() }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Networks</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 leading-none">{{ $totalTrails }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Trails</div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <div>
                <div class="text-2xl font-bold text-gray-900 leading-none">{{ $alwaysVisibleCount }}</div>
                <div class="text-xs text-gray-500 mt-1">Always Visible</div>
            </div>
        </div>
    </div>

    {{-- Networks List Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">All Networks</h2>
                <p class="text-xs text-gray-500">Click view or edit on any row</p>
            </div>
        </div>

        @if($networks->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Network Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trails</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visible</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($networks as $network)
                            @php
                                $season = $seasonStyles[$network->season] ?? $seasonStyles['both'];
                                $typeClass = $typeStyles[$network->type] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-600 border-2 border-white shadow-sm flex items-center justify-center text-base select-none flex-shrink-0">
                                            {{ $network->icon ?: '🏔️' }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $network->network_name }}</div>
                                            <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $season['class'] }}">
                                                {{ $season['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $network->address ?? 'N/A' }}</div>
                                    <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeClass }}">
                                        {{ ucwords(str_replace('_', ' ', $network->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-900">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 9m0 8V9m0 0V7"/>
                                        </svg>
                                        {{ $network->trails_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($network->is_always_visible)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Yes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.trail-networks.show', $network) }}"
                                           title="View"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.trail-networks.edit', $network) }}"
                                           title="Edit"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.trail-networks.destroy', $network) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this trail network?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Delete"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        @else
            <div class="px-6 py-16 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l3.273-3.273A6 6 0 1116.727 17.727L21 21H3z"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-600 mb-1">No trail networks yet</p>
                <a href="{{ route('admin.trail-networks.create') }}" class="text-sm text-green-600 hover:underline font-medium">
                    + Create your first network
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
