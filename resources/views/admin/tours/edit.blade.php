@extends('layouts.admin')

@section('title', 'Edit ' . $section['singular'])
@section('page-title', 'Edit ' . $section['singular'])

@section('content')

<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route($section['routePrefix'] . '.index') }}"
           class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 w-10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Edit {{ $section['singular'] }}</h2>
            <p class="text-sm text-muted-foreground">{{ $tour->title }}</p>
        </div>
    </div>

    <form action="{{ route($section['routePrefix'] . '.update', $tour) }}" method="POST" enctype="multipart/form-data" class="space-y-6"
        onkeydown="if(event.key==='Enter'&&event.target.tagName!=='TEXTAREA'){event.preventDefault();}">
        @csrf
        @method('PUT')
        @include('admin.tours._form', ['tour' => $tour])
    </form>

    {{-- Submitted by the Delete button in the form's sidebar (forms can't be nested). --}}
    <form id="delete-tour-form" method="POST" action="{{ route($section['routePrefix'] . '.destroy', $tour) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>

@include('admin.tours._scripts')
@endsection
