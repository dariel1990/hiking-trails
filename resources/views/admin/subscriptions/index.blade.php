@extends('layouts.admin')

@section('title', 'Subscriptions')
@section('page-title', 'Subscriptions')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Management</p>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Subscriptions</h2>
            <p class="mt-1 text-sm text-gray-500">Monitor active Pro subscribers across all platforms.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total Active --}}
        <div class="relative overflow-hidden rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-400 uppercase tracking-wider">Active</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-emerald-700">{{ $stats['active'] }}</p>
            <p class="mt-1 text-sm text-gray-500">active Pro subscribers</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-emerald-400"></div>
        </div>

        {{-- Android --}}
        <div class="relative overflow-hidden rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-400 uppercase tracking-wider">Android</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-blue-700">{{ $stats['android'] }}</p>
            <p class="mt-1 text-sm text-gray-500">via Google Play</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-blue-400"></div>
        </div>

        {{-- Web --}}
        <div class="relative overflow-hidden rounded-xl border border-violet-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50">
                    <svg class="h-5 w-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-violet-400 uppercase tracking-wider">Web</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-violet-700">{{ $stats['web'] }}</p>
            <p class="mt-1 text-sm text-gray-500">via Stripe</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-violet-400"></div>
        </div>

        {{-- Expiring Soon --}}
        <div class="relative overflow-hidden rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-amber-400 uppercase tracking-wider">Expiring</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-amber-700">{{ $stats['expiring_soon'] }}</p>
            <p class="mt-1 text-sm text-gray-500">expire within 7 days</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-amber-400"></div>
        </div>

    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">All subscriptions</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $subscriptions->total() }} {{ Str::plural('record', $subscriptions->total()) }} total</p>
            </div>
        </div>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="h-11 px-6 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">User</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Platform</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Plan</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Expires</th>
                        <th class="h-11 px-6 text-right align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($subscriptions as $subscription)
                        @php
                            $colors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-orange-400 to-rose-500','from-pink-500 to-fuchsia-600','from-amber-400 to-orange-500'];
                            $avatarGradient = $colors[($subscription->user?->id ?? 0) % count($colors)];

                            $isAndroid = $subscription->platform === 'android';
                            $isMonthly = str_contains($subscription->product_id, 'monthly');

                            $statusColors = [
                                'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                'in_grace_period' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                'on_hold' => 'bg-orange-50 text-orange-700 ring-orange-200',
                                'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
                                'expired' => 'bg-gray-100 text-gray-600 ring-gray-200',
                            ];
                            $statusColor = $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                            $statusLabel = ucfirst(str_replace('_', ' ', $subscription->status));

                            $expiresAt = $subscription->expires_at;
                            $isExpiringSoon = $expiresAt && $expiresAt->isFuture() && $expiresAt->diffInDays(now()) <= 7;
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/70">

                            {{-- User --}}
                            <td class="py-4 pl-6 pr-4 align-middle">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br {{ $avatarGradient }} text-xs font-bold text-white shadow-sm">
                                        {{ $subscription->user?->initials ?? '?' }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 leading-none">{{ $subscription->user?->name ?? 'Deleted user' }}</div>
                                        <div class="mt-0.5 text-xs text-gray-400 font-mono">{{ $subscription->user?->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Platform --}}
                            <td class="px-4 py-4 align-middle">
                                @if($isAndroid)
                                    <span class="inline-flex items-center gap-1.5 rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                        Android
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-200">
                                        Web
                                    </span>
                                @endif
                            </td>

                            {{-- Plan --}}
                            <td class="px-4 py-4 align-middle">
                                @if($isMonthly)
                                    <span class="inline-flex items-center gap-1.5 rounded bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                                        Pro Monthly
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                        Pro Annual
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 align-middle">
                                <span class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- Expires --}}
                            <td class="px-4 py-4 align-middle">
                                @if($expiresAt)
                                    <span class="text-sm tabular-nums {{ $isExpiringSoon ? 'font-semibold text-amber-600' : 'text-gray-500' }}">
                                        {{ $expiresAt->format('M j, Y') }}
                                        @if($isExpiringSoon)
                                            <span class="ml-1 text-xs font-normal text-amber-500">(soon)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 pl-4 pr-6 align-middle">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 active:scale-95">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900">No subscriptions yet</h3>
                                    <p class="mt-1 text-sm text-gray-400">Subscriptions will appear here once users sign up for Pro.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subscriptions->hasPages())
            <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-3">
                <p class="text-xs text-gray-400 tabular-nums">
                    Showing <span class="font-semibold text-gray-600">{{ $subscriptions->firstItem() }}</span>–<span class="font-semibold text-gray-600">{{ $subscriptions->lastItem() }}</span> of <span class="font-semibold text-gray-600">{{ $subscriptions->total() }}</span>
                </p>
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
