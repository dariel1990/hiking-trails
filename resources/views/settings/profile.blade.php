@extends('layouts.settings')

@section('title', 'Edit profile')

@section('settings-content')
<h2 class="text-xl font-bold text-gray-900 mb-8">Edit profile</h2>

<form method="POST" action="{{ route('settings.profile.avatar.update') }}" enctype="multipart/form-data" class="mb-2">
    @csrf

    <div class="relative inline-block">
        @if($user->avatar_url)
            <img src="{{ $user->avatar_url }}" alt="" class="h-24 w-24 rounded-full object-cover">
        @else
            <span class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-forest-600 to-forest-800 text-white text-2xl font-semibold">
                {{ $user->initials }}
            </span>
        @endif
        <label for="avatar" title="Change photo"
               class="absolute -bottom-1 -right-1 flex h-9 w-9 items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm text-gray-600 hover:text-forest-600 hover:border-forest-300 cursor-pointer transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only" onchange="this.form.submit()">
        </label>
    </div>
</form>
@error('avatar')
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
@enderror
@if($user->avatar)
    <form method="POST" action="{{ route('settings.profile.avatar.destroy') }}" class="mt-2 mb-8">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-xs text-gray-500 hover:text-red-600 underline">Remove photo</button>
    </form>
@else
    <div class="mb-8"></div>
@endif

<form method="POST" action="{{ route('settings.profile.update') }}">
    @csrf
    @method('PUT')

    <div class="grid sm:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1.5">First name</label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $firstName) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            @error('first_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1.5">Last name</label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $lastName) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            @error('last_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mb-10">
        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1.5">About me</label>
        <textarea id="bio" name="bio" rows="4" placeholder="About me"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('bio', $user->bio) }}</textarea>
        @error('bio')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="border-t border-gray-100 pt-8 flex items-center gap-3">
        <button type="submit"
                class="rounded-full bg-forest-600 hover:bg-forest-700 text-white font-semibold py-2.5 px-7 transition-colors">
            Save
        </button>
        <button type="reset"
                class="rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 px-7 transition-colors">
            Cancel
        </button>
    </div>
</form>
@endsection
