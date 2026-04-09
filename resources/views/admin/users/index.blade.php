@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Users</h2>
            <p class="text-sm text-muted-foreground">Manage admin and standard user accounts</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold">{{ $users->total() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Admins</p>
                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ \App\Models\User::where('is_admin', true)->count() }}</div>
        </div>
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6">
            <div class="flex items-center justify-between space-y-0 pb-2">
                <p class="text-sm font-medium text-muted-foreground">Standard Users</p>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="text-2xl font-bold">{{ \App\Models\User::where('is_admin', false)->count() }}</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">User</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Joined</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($users as $user)
                        <tr class="border-b transition-colors hover:bg-muted/50">

                            {{-- User --}}
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-semibold">
                                        {{ $user->initials }}
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="font-medium leading-none">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="ml-1 inline-flex items-center rounded-full border border-transparent bg-secondary px-2 py-0.5 text-xs font-semibold text-secondary-foreground">You</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-muted-foreground">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="p-4 align-middle">
                                @if($user->is_admin)
                                    <span class="inline-flex items-center rounded-full border border-transparent bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-transparent bg-secondary px-2.5 py-0.5 text-xs font-semibold text-secondary-foreground">
                                        Standard
                                    </span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td class="p-4 align-middle text-sm text-muted-foreground">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9 ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        title="Edit user">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-9 w-9 ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                                title="Delete user">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-input opacity-30 cursor-not-allowed" title="Cannot delete your own account">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="rounded-full bg-muted p-3">
                                        <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                    </div>
                                    <div class="space-y-2">
                                        <h3 class="text-lg font-semibold">No users found</h3>
                                        <p class="text-sm text-muted-foreground">Get started by creating the first user account.</p>
                                    </div>
                                    <a href="{{ route('admin.users.create') }}"
                                        class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add First User
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="flex items-center justify-between border-t px-6 py-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                </div>
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
