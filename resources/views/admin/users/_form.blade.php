{{-- Name --}}
<div class="grid gap-2">
    <label for="name" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
        Name <span class="text-red-500">*</span>
    </label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}"
        required autocomplete="name"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('name') border-red-500 @enderror">
    @error('name')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Email --}}
<div class="grid gap-2">
    <label for="email" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
        Email address <span class="text-red-500">*</span>
    </label>
    <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}"
        required autocomplete="email"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('email') border-red-500 @enderror">
    @error('email')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Password --}}
<div class="grid gap-2">
    <label for="password" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
        Password {{ isset($user) ? '(leave blank to keep current)' : '' }} <span class="text-red-500">{{ isset($user) ? '' : '*' }}</span>
    </label>
    <input type="password" id="password" name="password"
        {{ isset($user) ? '' : 'required' }} autocomplete="new-password"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 @error('password') border-red-500 @enderror">
    @error('password')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Password confirmation --}}
<div class="grid gap-2">
    <label for="password_confirmation" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
        Confirm password {{ isset($user) ? '(leave blank to keep current)' : '' }} <span class="text-red-500">{{ isset($user) ? '' : '*' }}</span>
    </label>
    <input type="password" id="password_confirmation" name="password_confirmation"
        {{ isset($user) ? '' : 'required' }} autocomplete="new-password"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
</div>

{{-- Admin toggle --}}
<div class="flex items-start gap-3 rounded-lg border p-4">
    <input type="hidden" name="is_admin" value="0">
    <input type="checkbox" id="is_admin" name="is_admin" value="1"
        {{ old('is_admin', $user->is_admin ?? false) ? 'checked' : '' }}
        class="mt-0.5 h-4 w-4 rounded border-input accent-primary">
    <div class="grid gap-1">
        <label for="is_admin" class="text-sm font-medium leading-none cursor-pointer">
            Administrator access
        </label>
        <p class="text-xs text-muted-foreground">
            Admins can manage all trails, facilities, media, and users.
        </p>
    </div>
</div>
