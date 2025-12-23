@extends('layouts.admin')

@section('title', 'Manage Facilities - ' . $trailNetwork->network_name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.trail-networks.show', $trailNetwork) }}" class="text-gray-600 hover:text-gray-900 mb-4 inline-block">
            ← Back to {{ $trailNetwork->network_name }}
        </a>
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manage Facilities</h1>
                <p class="text-gray-600 mt-1">{{ $trailNetwork->network_name }}</p>
            </div>
            <a href="{{ route('admin.trail-networks.facilities.create', $trailNetwork) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                + Add Facility
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Facilities Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($facilities as $facility)
            <div class="bg-white rounded-lg shadow-md p-4 border-l-4 
                {{ $facility->is_active ? 'border-green-500' : 'border-gray-300' }}">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl">{{ $facility->icon }}</span>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $facility->name }}</h3>
                            <span class="text-xs text-gray-500">{{ $facility->facility_type_label }}</span>
                        </div>
                    </div>
                    @if(!$facility->is_active)
                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Inactive</span>
                    @endif
                </div>

                @if($facility->description)
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($facility->description, 100) }}</p>
                @endif

                <div class="text-xs text-gray-500 mb-3">
                    <div class="font-mono">{{ $facility->latitude }}, {{ $facility->longitude }}</div>
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <a href="{{ route('admin.trail-networks.facilities.edit', [$trailNetwork, $facility]) }}" 
                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                        Edit
                    </a>
                    <form action="{{ route('admin.trail-networks.facilities.destroy', [$trailNetwork, $facility]) }}" 
                          method="POST" 
                          class="inline"
                          onsubmit="return confirm('Are you sure you want to delete this facility?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                </div>
                <p class="text-gray-600 mb-4">No facilities added yet.</p>
                <a href="{{ route('admin.trail-networks.facilities.create', $trailNetwork) }}" 
                   class="text-green-600 hover:underline font-medium">
                    Add your first facility
                </a>
            </div>
        @endforelse
    </div>

    <!-- Facility Types Reference -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 mb-2">Available Facility Types:</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 text-sm">
            <div>🅿️ Parking</div>
            <div>🚻 Toilets</div>
            <div>🏥 Emergency Kit</div>
            <div>🏠 Lodge</div>
            <div>👁️ Viewpoint</div>
            <div>ℹ️ Information</div>
            <div>🍽️ Picnic Area</div>
            <div>💧 Water</div>
            <div>⛺ Shelter</div>
        </div>
    </div>
</div>
@endsection