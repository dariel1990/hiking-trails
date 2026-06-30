@extends('layouts.admin')

@section('title', 'Edit Tour')
@section('page-title', 'Edit Tour')

@section('content')

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.tours.index') }}"
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Edit Tour</h2>
            <p class="text-sm text-muted-foreground">{{ $tour->title }}</p>
        </div>
    </div>

    <form action="{{ route('admin.tours.update', $tour) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.tours._form', ['tour' => $tour])
    </form>

    <!-- Danger Zone -->
    <div class="rounded-lg border border-red-200 bg-red-50">
        <div class="p-6">
            <h3 class="text-sm font-semibold text-red-800 mb-1">Danger Zone</h3>
            <p class="text-sm text-red-700 mb-4">Permanently delete this tour and all its stops.</p>
            <form method="POST" action="{{ route('admin.tours.destroy', $tour) }}"
                onsubmit="return confirm('Delete \'{{ $tour->title }}\'? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-700 h-9 px-4 py-2 text-sm font-medium transition-colors">
                    Delete Tour
                </button>
            </form>
        </div>
    </div>
</div>

@include('admin.tours._scripts')
@endsection
