@extends('layouts.admin')

@section('title', 'Legal Documents')
@section('page-title', 'Legal Documents')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Compliance</p>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">Legal Documents</h2>
        <p class="mt-1 text-sm text-gray-500">Publishing creates a new locked version. Past versions can never be edited.</p>
    </div>

    @include('admin.legal._flash')

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($documents as $document)
            <div class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $document['title'] }}</h3>
                    @if ($document['current'])
                        <p class="mt-1 text-sm text-gray-500">Version {{ $document['current']->version }}, published {{ $document['current']->published_at->format('F j, Y') }}</p>
                    @else
                        <p class="mt-1 text-sm text-gray-500">Not published yet</p>
                    @endif
                </div>

                @if ($document['draft'])
                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Unpublished draft, last saved {{ $document['draft']->updated_at->diffForHumans() }}</p>
                @endif

                <div class="mt-auto flex flex-wrap gap-3">
                    <a href="{{ route('admin.legal.draft', $document['type']) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ $document['draft'] ? 'Continue draft' : 'New version' }}</a>
                    <a href="{{ route('admin.legal.history', $document['type']) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Version history</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
