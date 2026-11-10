@extends('layouts.admin')

@section('title', 'Compare '.$title)
@section('page-title', 'Compare '.$title)

@php
    $rowClasses = [
        \App\Support\LineDiff::ADDED => 'bg-emerald-50 text-emerald-900',
        \App\Support\LineDiff::REMOVED => 'bg-red-50 text-red-900 line-through',
        \App\Support\LineDiff::EQUAL => 'text-gray-600',
    ];
    $rowMarkers = [\App\Support\LineDiff::ADDED => '+', \App\Support\LineDiff::REMOVED => '−', \App\Support\LineDiff::EQUAL => ''];
@endphp

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.legal.history', $type) }}" class="text-sm text-emerald-700 hover:underline">Version history</a>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Compare {{ $title }}</h2>
    </div>

    <form method="GET" class="flex flex-wrap items-end gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">From
            <select name="from" class="rounded-lg border-gray-300 text-sm">
                @foreach ($versions as $version)
                    <option value="{{ $version->version }}" @selected($version->version === $from->version)>v{{ $version->version }} ({{ $version->published_at->format('M j, Y') }})</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1 text-sm font-medium text-gray-700">To
            <select name="to" class="rounded-lg border-gray-300 text-sm">
                @foreach ($versions as $version)
                    <option value="{{ $version->version }}" @selected($version->version === $to->version)>v{{ $version->version }} ({{ $version->published_at->format('M j, Y') }})</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Compare</button>
        <p class="ml-auto text-sm text-gray-500">{{ $addedCount }} lines added, {{ $removedCount }} lines removed</p>
    </form>

    @if ($versions->count() < 2)
        <p class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm">There is only one version so far, so there is nothing to compare yet.</p>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white text-sm shadow-sm">
        @foreach ($rows as $row)
            <div class="flex gap-3 px-4 py-0.5 {{ $rowClasses[$row['type']] }}">
                <span class="w-3 shrink-0 select-none font-mono">{{ $rowMarkers[$row['type']] }}</span>
                <span class="min-h-5 whitespace-pre-wrap break-words">{{ $row['line'] }}</span>
            </div>
        @endforeach
    </div>
</div>
@endsection
