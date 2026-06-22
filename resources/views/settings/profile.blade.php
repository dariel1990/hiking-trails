@extends('layouts.settings')

@section('title', 'Profile')

@section('settings-content')
{{-- Section header --}}
<div class="border-b border-gray-100 px-7 py-5">
    <h2 class="text-base font-bold text-gray-900" style="font-family: 'Inter', sans-serif;">Profile</h2>
    <p class="text-sm text-gray-400 mt-0.5">Manage how others see you on XploreSmithers.</p>
</div>

<div class="px-7 py-8 space-y-10">

    {{-- Avatar section --}}
    <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">Profile photo</p>
        <div class="flex items-center gap-6">
            <div class="relative shrink-0">
                @if($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="h-20 w-20 rounded-2xl object-cover ring-2 ring-gray-100">
                @else
                    <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-forest-600 to-forest-800 text-white text-2xl font-bold ring-2 ring-gray-100">
                        {{ $user->initials }}
                    </span>
                @endif

                <form method="POST" action="{{ route('settings.profile.avatar.update') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="avatar" title="Change photo"
                           class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-gray-200 shadow-md text-gray-500 hover:text-gray-900 cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" class="sr-only" onchange="this.form.submit()">
                    </label>
                </form>
            </div>

            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $user->name ?: 'Your name' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">JPG, PNG or WebP. Max 2 MB.</p>
                @if($user->avatar)
                    <form method="POST" action="{{ route('settings.profile.avatar.destroy') }}" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-gray-400 hover:text-red-500 transition-colors">Remove photo</button>
                    </form>
                @endif
            </div>
        </div>
        @error('avatar')
            <p class="mt-3 text-sm text-red-500 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Personal info form --}}
    <form method="POST" action="{{ route('settings.profile.update') }}">
        @csrf
        @method('PUT')

        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">Personal information</p>

        <div class="grid sm:grid-cols-2 gap-5 mb-5">
            <div class="grid gap-1.5">
                <label for="first_name" class="text-xs font-semibold text-gray-600">First name</label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $firstName) }}"
                       placeholder="First"
                       class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('first_name') border-red-400 bg-red-50 @enderror">
                @error('first_name')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid gap-1.5">
                <label for="last_name" class="text-xs font-semibold text-gray-600">Last name</label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $lastName) }}"
                       placeholder="Last"
                       class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-300 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('last_name') border-red-400 bg-red-50 @enderror">
                @error('last_name')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-1.5 mb-8">
            <label for="bio" class="text-xs font-semibold text-gray-600">Bio</label>
            <textarea id="bio" name="bio" rows="4"
                      placeholder="Tell others a little about yourself…"
                      class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-300 resize-none transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('bio') border-red-400 bg-red-50 @enderror">{{ old('bio', $user->bio) }}</textarea>
            <p class="text-xs text-gray-400">Brief description shown on your profile. Max 500 characters.</p>
            @error('bio')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                Save changes
            </button>
            <button type="reset"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 active:scale-95">
                Reset
            </button>
        </div>

        @if(session('profile_success') || (session('status') && request()->routeIs('settings.profile')))
            <p class="mt-3 flex items-center gap-1.5 text-sm text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Profile saved.
            </p>
        @endif
    </form>

</div>
@endsection
