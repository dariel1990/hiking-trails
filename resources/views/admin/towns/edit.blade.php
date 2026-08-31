@extends('layouts.admin')

@section('title', 'Edit '.$town->name)
@section('page-title', 'Edit Town')

@section('content')
<form action="{{ route('admin.towns.update', $town) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">{{ $town->name }}, {{ $town->province_code }}</h2>
            <p class="text-sm text-muted-foreground"><code>/hiking-trails/{{ $town->slug }}</code></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.towns.index') }}"
               class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-10 px-4 py-2 text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-md bg-black text-white hover:bg-black/90 h-10 px-4 py-2 text-sm font-medium transition-colors">
                Save Changes
            </button>
        </div>
    </div>

    @include('admin.towns._form')
</form>

@if($town->hero_image)
    <form id="delete-hero-form" action="{{ route('admin.towns.hero-image.delete', $town) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif
@endsection
