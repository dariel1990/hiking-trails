@extends('layouts.public')

@section('title', $pageTitle)

@push('meta')
    <meta name="description" content="{{ $metaDescription }}">
@endpush

@push('styles')
    @include('legal._styles')
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 text-white py-16 px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl font-bold mb-3 text-white">{{ $document->title() }}</h1>
            <p class="text-emerald-300 text-sm">Last updated: {{ $document->published_at->format('F j, Y') }} &middot; Version {{ $document->version }}</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-3xl mx-auto px-4 py-14">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-8 py-8">
            <div class="legal-content">{!! $html !!}</div>
        </div>

        {{-- Back link --}}
        <div class="mt-8 text-center">
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 text-sm text-emerald-700 hover:text-emerald-900 font-medium transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
