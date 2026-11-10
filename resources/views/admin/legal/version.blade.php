@extends('layouts.admin')

@section('title', $document->title().' v'.$document->version)
@section('page-title', $document->title().' v'.$document->version)

@section('content')
@include('legal._styles')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.legal.history', $document->type) }}" class="text-sm text-emerald-700 hover:underline">Version history</a>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $document->title() }} v{{ $document->version }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            Published {{ $document->published_at->format('F j, Y') }}@if ($document->publisher) by {{ $document->publisher->name }}@endif
            &middot; {{ $document->is_material ? 'Material change' : 'Non-material change' }}
        </p>
        <p class="mt-2 text-sm text-gray-700">{{ $document->change_summary }}</p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <div class="legal-content">{!! $html !!}</div>
    </div>
</div>
@endsection
