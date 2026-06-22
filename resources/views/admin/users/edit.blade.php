@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
@php
    $colors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-orange-400 to-rose-500','from-pink-500 to-fuchsia-600','from-amber-400 to-orange-500'];
    $avatarGradient = $colors[$user->id % count($colors)];
@endphp

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                <a href="{{ route('admin.users.index') }}" class="hover:text-gray-600 transition-colors">Users</a>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-600">{{ $user->name }}</span>
            </nav>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Edit account</h2>
            <p class="mt-1 text-sm text-gray-500">Update details, permissions, or access for this user.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900 active:scale-95">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to users
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main form --}}
            <div class="lg:col-span-2 space-y-px">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/60 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Account details</h3>
                        <p class="text-xs text-gray-400 mt-0.5">All changes are saved immediately on submit.</p>
                    </div>
                    <div class="p-6 space-y-6">
                        @include('admin.users._form')
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                {{-- User card --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="h-16 bg-gradient-to-r {{ $avatarGradient }}"></div>
                    <div class="px-5 pb-5">
                        <div class="-mt-6 mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $avatarGradient }} text-sm font-bold text-white shadow-lg ring-2 ring-white">
                            {{ $user->initials }}
                        </div>
                        <p class="font-semibold text-gray-900 leading-tight">{{ $user->name }}</p>
                        <p class="text-xs font-mono text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @if($user->is_admin)
                                <span class="inline-flex items-center rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Admin</span>
                            @else
                                <span class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-200">Member</span>
                            @endif
                            @if($user->is_active)
                                <span class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                    <span class="h-1 w-1 rounded-full bg-emerald-500"></span>Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200">
                                    <span class="h-1 w-1 rounded-full bg-red-500"></span>Suspended
                                </span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs text-gray-400 tabular-nums">Joined {{ $user->created_at->format('M j, Y') }}</p>
                    </div>
                </div>

                {{-- Save --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-2">
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 hover:shadow-md active:scale-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save changes
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 hover:text-gray-900 active:scale-95">
                        Cancel
                    </a>
                </div>

                {{-- Danger zone --}}
                @if($user->id !== auth()->id())
                    <div class="rounded-xl border border-red-100 bg-white shadow-sm overflow-hidden">
                        <div class="border-b border-red-100 bg-red-50/50 px-5 py-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-red-600">Danger zone</h3>
                        </div>
                        <div class="p-5">
                            <p class="mb-4 text-xs text-gray-500 leading-relaxed">Permanently delete this user account and all associated data. This cannot be undone.</p>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm('Remove {{ addslashes($user->name) }}? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition-all hover:bg-red-600 hover:text-white hover:border-red-600 hover:shadow-sm active:scale-95">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove account
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
