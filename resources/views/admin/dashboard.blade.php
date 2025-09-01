@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="admin-card">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Trails</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_trails'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Featured Trails</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['featured_trails'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Trails</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['active_trails'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Photos</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_photos'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="admin-card">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.trails.create') }}" class="admin-button-primary block text-center">
                    Add New Trail
                </a>
                <a href="{{ route('admin.trails.index') }}" class="admin-button-secondary block text-center">
                    Manage All Trails
                </a>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Trails</h3>
            @if($stats['recent_trails']->count() > 0)
                <div class="space-y-3">
                    @foreach($stats['recent_trails'] as $trail)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $trail->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $trail->location ?? 'Location TBD' }}</p>
                        </div>
                        <a href="{{ route('admin.trails.show', $trail) }}" 
                           class="text-primary-600 hover:text-primary-700 text-sm">
                            Edit
                        </a>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No trails created yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection