@php $isEdit = isset($user); @endphp

{{-- Section: Identity --}}
<div class="space-y-4">
    <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gray-900 text-white">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Identity</h4>
            <p class="text-xs text-gray-400">Name and contact information</p>
        </div>
    </div>

    {{-- Full name --}}
    <div class="grid gap-1.5">
        <label for="name" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
            Full name <span class="text-red-400 normal-case tracking-normal font-normal">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}"
            required autocomplete="name" placeholder="e.g. Alex Rivera"
            class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('name') border-red-400 bg-red-50 @else border-gray-200 @enderror">
        @error('name')
            <p class="text-xs text-red-500 flex items-center gap-1">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- First / Last --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-1.5">
            <label for="first_name" class="text-xs font-semibold uppercase tracking-wider text-gray-500">First name</label>
            <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}"
                autocomplete="given-name" placeholder="First"
                class="flex h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('first_name') border-red-400 bg-red-50 @enderror">
            @error('first_name')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="grid gap-1.5">
            <label for="last_name" class="text-xs font-semibold uppercase tracking-wider text-gray-500">Last name</label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}"
                autocomplete="family-name" placeholder="Last"
                class="flex h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('last_name') border-red-400 bg-red-50 @enderror">
            @error('last_name')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Email --}}
    <div class="grid gap-1.5">
        <label for="email" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
            Email address <span class="text-red-400 normal-case tracking-normal font-normal">*</span>
        </label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}"
            required autocomplete="email" placeholder="name@example.com"
            class="flex h-10 w-full rounded-lg border px-3 py-2 text-sm text-gray-900 bg-white font-mono placeholder:text-gray-300 placeholder:font-sans transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('email') border-red-400 bg-red-50 @else border-gray-200 @enderror">
        @error('email')
            <p class="text-xs text-red-500 flex items-center gap-1">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Phone --}}
    <div class="grid gap-1.5">
        <label for="phone" class="text-xs font-semibold uppercase tracking-wider text-gray-500">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
            autocomplete="tel" placeholder="+1 (000) 000-0000"
            class="flex h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('phone') border-red-400 bg-red-50 @enderror">
        @error('phone')
            <p class="text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Bio --}}
    <div class="grid gap-1.5">
        <label for="bio" class="text-xs font-semibold uppercase tracking-wider text-gray-500">Bio</label>
        <textarea id="bio" name="bio" rows="3" placeholder="Short description about this user…"
            class="flex w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors resize-none focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('bio') border-red-400 bg-red-50 @enderror">{{ old('bio', $user->bio ?? '') }}</textarea>
        @error('bio')
            <p class="text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Section: Security --}}
<div class="space-y-4 pt-2">
    <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gray-900 text-white">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Security</h4>
            <p class="text-xs text-gray-400">{{ $isEdit ? 'Leave blank to keep the current password' : 'Set a strong password for this account' }}</p>
        </div>
    </div>

    {{-- Password --}}
    <div class="grid gap-1.5">
        <label for="password" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
            Password @if(!$isEdit)<span class="text-red-400 normal-case tracking-normal font-normal">*</span>@endif
        </label>
        <input type="password" id="password" name="password"
            {{ !$isEdit ? 'required' : '' }} autocomplete="new-password" placeholder="{{ $isEdit ? '••••••••' : 'Min. 8 characters' }}"
            class="flex h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent @error('password') border-red-400 bg-red-50 @enderror">
        @error('password')
            <p class="text-xs text-red-500 flex items-center gap-1">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Confirm password --}}
    <div class="grid gap-1.5">
        <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-gray-500">
            Confirm password @if(!$isEdit)<span class="text-red-400 normal-case tracking-normal font-normal">*</span>@endif
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation"
            {{ !$isEdit ? 'required' : '' }} autocomplete="new-password" placeholder="{{ $isEdit ? '••••••••' : 'Repeat password' }}"
            class="flex h-10 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 bg-white placeholder:text-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
    </div>
</div>

{{-- Section: Permissions --}}
<div class="space-y-3 pt-2">
    <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-gray-900 text-white">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Permissions</h4>
            <p class="text-xs text-gray-400">Role and account access control</p>
        </div>
    </div>

    {{-- Admin toggle --}}
    <label for="is_admin" class="flex cursor-pointer items-start gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 transition-colors hover:bg-gray-100/70">
        <input type="hidden" name="is_admin" value="0">
        <input type="checkbox" id="is_admin" name="is_admin" value="1"
            {{ old('is_admin', $user->is_admin ?? false) ? 'checked' : '' }}
            class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-gray-900 accent-gray-900 focus:ring-gray-900">
        <div>
            <span class="text-sm font-semibold text-gray-900">Administrator access</span>
            <p class="mt-0.5 text-xs text-gray-500">Can manage trails, facilities, media, and all user accounts.</p>
        </div>
    </label>

    {{-- Active toggle --}}
    <label for="is_active" class="flex cursor-pointer items-start gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 transition-colors hover:bg-gray-100/70">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
            {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}
            class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-gray-900 accent-gray-900 focus:ring-gray-900">
        <div>
            <span class="text-sm font-semibold text-gray-900">Account active</span>
            <p class="mt-0.5 text-xs text-gray-500">Deactivated accounts are blocked from logging in immediately.</p>
        </div>
    </label>
</div>
