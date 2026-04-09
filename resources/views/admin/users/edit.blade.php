@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <a href="{{ route('admin.users.index') }}" class="hover:text-foreground transition-colors">Users</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span>{{ $user->name }}</span>
            </div>
            <h2 class="text-2xl font-semibold tracking-tight">Edit User</h2>
            <p class="text-sm text-muted-foreground">Update account details for {{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Users
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Form fields --}}
            <div class="lg:col-span-2">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold leading-none tracking-tight">Account Details</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @include('admin.users._form')
                    </div>
                </div>
            </div>

            {{-- Save + danger panel --}}
            <div class="space-y-4">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold leading-none tracking-tight">Save</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Save Changes
                        </button>
                        <a href="{{ route('admin.users.index') }}"
                            class="inline-flex w-full items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Danger zone --}}
                @if($user->id !== auth()->id())
                    <div class="rounded-lg border border-red-200 bg-card text-card-foreground shadow-sm">
                        <div class="border-b border-red-200 px-6 py-4">
                            <h3 class="font-semibold leading-none tracking-tight text-red-700">Danger Zone</h3>
                        </div>
                        <div class="p-6">
                            <p class="mb-3 text-xs text-muted-foreground">Permanently delete this user account. This cannot be undone.</p>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </form>

</div>
@endsection
