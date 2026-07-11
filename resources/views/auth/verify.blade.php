@extends('layouts.public')

@section('title', 'Verify Your Email')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm text-center">
        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
            <svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Check your email</h1>
        <p class="mt-3 text-sm text-gray-600">
            We sent a verification link to
            <span class="font-medium text-gray-900">{{ auth()->user()->email }}</span>.
            Click the link in that email to activate every feature of your account.
        </p>

        @if (session('resent'))
            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                A fresh verification link has been sent to your email address.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.resend') }}" class="mt-6">
            @csrf
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Resend verification email
            </button>
        </form>

        <p class="mt-4 text-xs text-gray-500">
            Didn't get it? Check your spam folder, or resend the link above.
        </p>
    </div>
</div>
@endsection
