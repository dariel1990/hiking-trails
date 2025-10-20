@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="admin-card">
            <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="card-title">Total Trails</h3>
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                </div>
            </div>
            <div class="card-content">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_trails'] }}</div>
                <p class="text-xs text-gray-600">
                    +2 from last month
                </p>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="card-title">Featured Trails</h3>
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
            </div>
            <div class="card-content">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['featured_trails'] }}</div>
                <p class="text-xs text-gray-600">
                    {{ round(($stats['featured_trails'] / max($stats['total_trails'], 1)) * 100) }}% of total trails
                </p>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="card-title">Active Trails</h3>
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="card-content">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['active_trails'] }}</div>
                <p class="text-xs text-gray-600">
                    {{ $stats['total_trails'] - $stats['active_trails'] }} trails closed
                </p>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                <h3 class="card-title">Total Photos</h3>
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="card-content">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_photos'] }}</div>
                <p class="text-xs text-gray-600">
                    +12 this week
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
                <p class="card-description">Common tasks and shortcuts</p>
            </div>
            <div class="card-content space-y-3">
                <a href="{{ route('admin.trails.create') }}" class="admin-button-primary w-full justify-start">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add New Trail
                </a>
                <a href="{{ route('admin.trails.index') }}" class="admin-button-secondary w-full justify-start">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Manage All Trails
                </a>
                <button class="btn-outline w-full justify-start">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Photos
                </button>
                <button class="btn-ghost w-full justify-start text-gray-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Data
                </button>
            </div>
        </div>

        <!-- Recent Trails -->
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title">Recent Trails</h3>
                <p class="card-description">Latest trail additions and updates</p>
            </div>
            <div class="card-content">
                @if($stats['recent_trails']->count() > 0)
                    <div class="space-y-4">
                        @foreach($stats['recent_trails'] as $trail)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                    @if($trail->featuredPhoto)
                                        <img src="{{ $trail->featuredPhoto->url }}" alt="{{ $trail->name }}" class="w-10 h-10 rounded-lg object-cover">
                                    @else
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $trail->name }}</p>
                                    <p class="text-xs text-gray-600 truncate">{{ $trail->location ?? 'Location TBD' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($trail->is_featured)
                                    <span class="badge badge-default">Featured</span>
                                @endif
                                <a href="{{ route('admin.trails.show', $trail) }}" class="btn-ghost btn-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600">No trails created yet.</p>
                        <a href="{{ route('admin.trails.create') }}" class="admin-button-primary btn-sm mt-3">
                            Create Your First Trail
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- GPX Statistics Card -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Data Sources</h3>
            <p class="card-description">Trail data origin breakdown</p>
        </div>
        <div class="card-content">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">GPX Imported</span>
                    </div>
                    <span class="text-sm font-semibold">{{ $stats['gpx_trails'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">Manually Created</span>
                    </div>
                    <span class="text-sm font-semibold">{{ $stats['manual_trails'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                        <span class="text-sm text-gray-700">Mixed Sources</span>
                    </div>
                    <span class="text-sm font-semibold">{{ $stats['mixed_trails'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="admin-card">
        <div class="card-header">
            <h3 class="card-title">Recent Activity</h3>
            <p class="card-description">Latest changes and updates to your trails</p>
        </div>
        <div class="card-content">
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">
                            <span class="font-medium">System</span> updated trail difficulty algorithm
                        </p>
                        <p class="text-xs text-gray-600">2 hours ago</p>
                    </div>
                </div>
                
                @if($stats['recent_trails']->count() > 0)
                    @foreach($stats['recent_trails']->take(3) as $trail)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                New trail <span class="font-medium">{{ $trail->name }}</span> was created
                            </p>
                            <p class="text-xs text-gray-600">{{ $trail->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                @endif
                
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900">
                            <span class="font-medium">Admin</span> updated system settings
                        </p>
                        <p class="text-xs text-gray-600">1 day ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection