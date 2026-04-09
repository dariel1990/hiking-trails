@extends('layouts.admin')

@section('title', 'Add User')
@section('page-title', 'Add User')

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
                <span>Add User</span>
            </div>
            <h2 class="text-2xl font-semibold tracking-tight">Add User</h2>
            <p class="text-sm text-muted-foreground">Create a new admin or standard user account</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Users
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
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

            {{-- Save panel --}}
            <div class="space-y-4">
                <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold leading-none tracking-tight">Save</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}"
                            class="inline-flex w-full items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
