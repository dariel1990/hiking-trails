@extends('layouts.admin')

@section('title', 'Manage Trails')
@section('page-title', 'Trails')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <form method="GET" action="{{ route('admin.trails.index') }}" class="flex space-x-2">
                <input type="text" name="search" placeholder="Search trails..." 
                       value="{{ request('search') }}"
                       class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="seasonal" {{ request('status') === 'seasonal' ? 'selected' : '' }}>Seasonal</option>
                </select>
                <button type="submit" class="admin-button-primary">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.trails.index') }}" class="admin-button-secondary">Clear</a>
                @endif
            </form>
        </div>
        <a href="{{ route('admin.trails.create') }}" class="admin-button-primary">
            Add New Trail
        </a>
    </div>
</div>

<div class="admin-card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trail</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photos</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($trails as $trail)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($trail->featuredPhoto)
                                <img class="h-10 w-10 rounded-lg object-cover mr-3" src="{{ $trail->featuredPhoto->url }}" alt="{{ $trail->name }}">
                            @else
                                <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $trail->name }}</div>
                                @if($trail->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Featured
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $trail->location ?? 'Not specified' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-900">{{ $trail->difficulty_level }}/5</span>
                        <div class="text-xs text-gray-500">{{ $trail->difficulty_text }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $trail->distance_km }} km
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'closed' => 'bg-red-100 text-red-800',
                                'seasonal' => 'bg-yellow-100 text-yellow-800'
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$trail->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($trail->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $trail->photos->count() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="{{ route('admin.trails.show', $trail) }}" class="text-primary-600 hover:text-primary-900">View</a>
                        <a href="{{ route('admin.trails.edit', $trail) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        <form method="POST" action="{{ route('admin.trails.destroy', $trail) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this trail?')"
                                    class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <h3 class="mt-4 text-sm font-medium text-gray-900">No trails found</h3>
                        <p class="mt-2 text-sm text-gray-500">Get started by creating your first trail.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.trails.create') }}" class="admin-button-primary">
                                Add New Trail
                            </a>
                        </div>
                    </td>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($trails->hasPages())
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            {{ $trails->links() }}
        </div>
    @endif
</div>
@endsection