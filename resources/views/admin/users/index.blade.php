@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Management</p>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">User accounts</h2>
            <p class="mt-1 text-sm text-gray-500">Control access, roles, and account status across all users.</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 hover:shadow-md active:scale-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add user
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total --}}
        <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100">
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-gray-900">{{ $users->total() }}</p>
            <p class="mt-1 text-sm text-gray-500">registered accounts</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gray-200"></div>
        </div>

        {{-- Active --}}
        <div class="relative overflow-hidden rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-400 uppercase tracking-wider">Active</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-emerald-700">{{ \App\Models\User::where('is_active', true)->count() }}</p>
            <p class="mt-1 text-sm text-gray-500">can log in</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-emerald-400"></div>
        </div>

        {{-- Admins --}}
        <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-400 uppercase tracking-wider">Admins</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-blue-700">{{ \App\Models\User::where('is_admin', true)->count() }}</p>
            <p class="mt-1 text-sm text-gray-500">with full access</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-blue-400"></div>
        </div>

        {{-- Deactivated --}}
        <div class="relative overflow-hidden rounded-xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-red-400 uppercase tracking-wider">Suspended</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-red-600">{{ \App\Models\User::where('is_active', false)->count() }}</p>
            <p class="mt-1 text-sm text-gray-500">blocked from login</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-red-400"></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        {{-- Table toolbar --}}
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">All accounts</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $users->total() }} {{ Str::plural('user', $users->total()) }} total</p>
            </div>
        </div>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="h-11 px-6 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Role</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Joined</th>
                        <th class="h-11 px-6 text-right align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                        @php
                            $colors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-orange-400 to-rose-500','from-pink-500 to-fuchsia-600','from-amber-400 to-orange-500'];
                            $avatarGradient = $colors[$user->id % count($colors)];
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/70">

                            {{-- User --}}
                            <td class="py-4 pl-6 pr-4 align-middle">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br {{ $avatarGradient }} text-xs font-bold text-white shadow-sm">
                                        {{ $user->initials }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-900 leading-none">{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="inline-flex items-center rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">you</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-xs text-gray-400 font-mono">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="px-4 py-4 align-middle">
                                @if($user->is_admin)
                                    <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">
                                        Member
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 align-middle">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        </span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-400"></span>
                                        Suspended
                                    </span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td class="px-4 py-4 align-middle">
                                <span class="text-sm tabular-nums text-gray-500">{{ $user->created_at->format('M j, Y') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 pl-4 pr-6 align-middle">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 active:scale-95"
                                        title="Edit user">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Remove {{ addslashes($user->name) }}? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-transparent px-3 py-1.5 text-xs font-medium text-gray-400 transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 active:scale-95"
                                                title="Delete user">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-300 cursor-not-allowed" title="Cannot remove your own account">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remove
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900">No accounts yet</h3>
                                    <p class="mt-1 text-sm text-gray-400">Create the first user account to get started.</p>
                                    <a href="{{ route('admin.users.create') }}"
                                        class="mt-5 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add first user
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
            <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-3">
                <p class="text-xs text-gray-400 tabular-nums">
                    Showing <span class="font-semibold text-gray-600">{{ $users->firstItem() }}</span>–<span class="font-semibold text-gray-600">{{ $users->lastItem() }}</span> of <span class="font-semibold text-gray-600">{{ $users->total() }}</span>
                </p>
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
