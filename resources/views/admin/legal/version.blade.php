@extends('layouts.admin')

@section('title', $document->title().' v'.$document->version)
@section('page-title', $document->title().' v'.$document->version)

@section('content')
@include('legal._styles')
@php
    $tabUrl = fn (string $name): string => route('admin.legal.version', [$document->type, $document->version, 'tab' => $name]);
    $tabClass = fn (string $name): string => $tab === $name
        ? 'border-emerald-600 text-emerald-700'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700';
@endphp
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.legal.history', $document->type) }}" class="text-sm text-emerald-700 hover:underline">Version history</a>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $document->title() }} v{{ $document->version }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            Published {{ $document->published_at->format('F j, Y') }}@if ($document->publisher) by {{ $document->publisher->name }}@endif
            &middot; {{ $document->is_material ? 'Material change' : 'Non-material change' }}
            @if ($isCurrent) &middot; <span class="font-medium text-emerald-700">Current version</span>@endif
        </p>
        <p class="mt-2 text-sm text-gray-700">{{ $document->change_summary }}</p>
    </div>

    <nav class="flex gap-6 border-b border-gray-200 text-sm font-medium">
        <a href="{{ $tabUrl('accepted') }}" class="-mb-px border-b-2 px-1 pb-3 {{ $tabClass('accepted') }}">
            Accepted <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ number_format($acceptedCount) }}</span>
        </a>
        @if ($isCurrent)
            <a href="{{ $tabUrl('pending') }}" class="-mb-px border-b-2 px-1 pb-3 {{ $tabClass('pending') }}">
                Still pending <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $pendingCount > 0 ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600' }}">{{ number_format($pendingCount) }}</span>
            </a>
        @endif
        <a href="{{ $tabUrl('document') }}" class="-mb-px border-b-2 px-1 pb-3 {{ $tabClass('document') }}">Document</a>
    </nav>

    @if ($tab === 'document')
        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <div class="legal-content">{!! $html !!}</div>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $tab === 'pending' ? 'Users who still need to accept' : 'Users who accepted this version' }}</h3>
                    <p class="mt-0.5 text-xs text-gray-400">
                        {{ $tab === 'pending'
                            ? 'They see a red dot in their account menu on the website, or a prompt in the app.'
                            : 'Recorded when the user agreed, with where they agreed from.' }}
                    </p>
                </div>
                <form method="GET" class="flex gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search name or email"
                           class="w-56 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <button type="submit" class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Search</button>
                </form>
            </div>

            <div class="relative w-full overflow-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            <th class="h-11 px-6">User</th>
                            @if ($tab === 'pending')
                                <th class="h-11 px-4">Joined</th>
                            @else
                                <th class="h-11 px-4">Accepted</th>
                                <th class="h-11 px-4">Where</th>
                                <th class="h-11 px-4">IP address</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @if ($tab === 'pending')
                            @forelse ($pendingUsers as $user)
                                <tr class="hover:bg-gray-50/70">
                                    <td class="px-6 py-3">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-gray-900 hover:text-emerald-700">{{ $user->name }}</a>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->created_at?->format('M j, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-6 py-10 text-center text-sm text-gray-500">{{ $search !== '' ? 'No pending users match your search.' : 'Everyone is up to date.' }}</td></tr>
                            @endforelse
                        @else
                            @forelse ($acceptances as $acceptance)
                                <tr class="hover:bg-gray-50/70">
                                    <td class="px-6 py-3">
                                        @if ($acceptance->user)
                                            <a href="{{ route('admin.users.edit', $acceptance->user) }}" class="font-medium text-gray-900 hover:text-emerald-700">{{ $acceptance->user->name }}</a>
                                            <p class="text-xs text-gray-500">{{ $acceptance->user->email }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600" title="{{ $acceptance->accepted_at?->toDayDateTimeString() }}">{{ $acceptance->accepted_at?->format('M j, Y g:i A') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $acceptance->source === \App\Models\LegalAcceptance::SOURCE_WEB ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                            {{ $acceptance->source === \App\Models\LegalAcceptance::SOURCE_WEB ? 'Website' : 'App' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $acceptance->ip_address ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">{{ $search !== '' ? 'No acceptances match your search.' : 'No one has accepted this version yet.' }}</td></tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            @php($paginator = $tab === 'pending' ? $pendingUsers : $acceptances)
            @if ($paginator->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">{{ $paginator->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
