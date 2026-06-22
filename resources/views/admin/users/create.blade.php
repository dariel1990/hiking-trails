@extends('layouts.admin')

@section('title', 'Add User')
@section('page-title', 'Add User')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                <a href="{{ route('admin.users.index') }}" class="hover:text-gray-600 transition-colors">Users</a>
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-600">New account</span>
            </nav>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Create account</h2>
            <p class="mt-1 text-sm text-gray-500">Set up a new admin or standard user account.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900 active:scale-95">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to users
        </a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Main form --}}
            <div class="lg:col-span-2">
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50/60 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900" style="font-family: 'Inter', sans-serif;">Account details</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Fill in the information for this new account.</p>
                    </div>
                    <div class="p-6 space-y-6">
                        @include('admin.users._form')
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">

                {{-- Info card --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-900 text-white mb-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">New account checklist</h4>
                    <ul class="space-y-2 mt-3">
                        <li class="flex items-start gap-2 text-xs text-gray-500">
                            <svg class="h-3.5 w-3.5 mt-0.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Use a real email address — the user will need it to log in and reset their password.
                        </li>
                        <li class="flex items-start gap-2 text-xs text-gray-500">
                            <svg class="h-3.5 w-3.5 mt-0.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Set a temporary password and let the user change it after logging in.
                        </li>
                        <li class="flex items-start gap-2 text-xs text-gray-500">
                            <svg class="h-3.5 w-3.5 mt-0.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Admin access grants full control — only enable for trusted team members.
                        </li>
                    </ul>
                </div>

                {{-- Save --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-2">
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-gray-700 hover:shadow-md active:scale-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create account
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 hover:text-gray-900 active:scale-95">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>

</div>
@endsection
