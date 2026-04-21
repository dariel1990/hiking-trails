@extends('layouts.admin')

@section('title', 'Add Business')
@section('page-title', 'Add Business')

@section('content')

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.businesses.index') }}"
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Add Business</h2>
            <p class="text-sm text-muted-foreground">Create a new business listing for the map</p>
        </div>
    </div>

    <form action="{{ route('admin.businesses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('admin.businesses._form', ['business' => null])
    </form>
</div>

@include('admin.businesses._scripts')
@endsection
