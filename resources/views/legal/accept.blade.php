@extends('layouts.public')

@section('title', 'Review our policies — Xplore Smithers')

@push('styles')
    @include('legal._styles')
    @include('legal._review_script')
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen px-4 py-10">
    <div class="max-w-2xl mx-auto flex flex-col gap-5 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Please review our policies</h1>
            <p class="mt-1 text-sm text-gray-600">We've made changes. Please take a moment to review and accept them.</p>
        </div>

        @error('versions')
            <p class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">{{ $message }}</p>
        @enderror
        @error('accepted')
            <p class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('legal.accept.store') }}" x-data x-ref="acceptForm" @legal-review-complete.window="$refs.acceptForm.submit()">
            @csrf
            <input type="hidden" name="accepted" value="1">
            @foreach ($documents as $document)
                <input type="hidden" name="versions[]" value="{{ $document['id'] }}">
            @endforeach

            @include('legal._review', ['documents' => $documents])
        </form>

        <a href="{{ $returnUrl }}" class="text-center text-sm text-gray-500 hover:text-gray-700">Not now</a>
    </div>
</div>
@endsection
