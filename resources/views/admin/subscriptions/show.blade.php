@extends('layouts.admin')

@section('title', 'Subscription Detail')
@section('page-title', 'Subscription Detail')

@section('content')
@php
    $platformLabel = $subscription->platformLabel();
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

    $isCancellable = ! in_array($subscription->status, ['cancelled', 'expired']);
@endphp

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.subscriptions.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 active:scale-95">
            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-0.5">Management</p>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Subscription #{{ $subscription->id }}</h2>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- Left: Subscription details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Details card --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Subscription details</h3>
                </div>
                <dl class="divide-y divide-gray-50">
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Status</dt>
                        <dd>
                            <span class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Platform</dt>
                        <dd>
                            @php
                                $platformBadge = match($subscription->platform) {
                                    'android' => 'bg-blue-50 text-blue-700 ring-blue-200',
                                    'ios' => 'bg-slate-100 text-slate-700 ring-slate-300',
                                    default => 'bg-violet-50 text-violet-700 ring-violet-200',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $platformBadge }}">{{ $platformLabel }}</span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Plan</dt>
                        <dd>
                            @if($isMonthly)
                                <span class="inline-flex items-center gap-1.5 rounded bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200">
                                    Pro Monthly
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                    Pro Annual
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Product ID</dt>
                        <dd class="font-mono text-xs text-gray-500">{{ $subscription->product_id }}</dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Auto-renewing</dt>
                        <dd class="text-sm text-gray-900">{{ $subscription->auto_renewing ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Expires</dt>
                        <dd class="text-sm tabular-nums text-gray-900">
                            {{ $subscription->expires_at ? $subscription->expires_at->format('M j, Y g:ia T') : '—' }}
                        </dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Created</dt>
                        <dd class="text-sm tabular-nums text-gray-900">{{ $subscription->created_at->format('M j, Y g:ia T') }}</dd>
                    </div>
                    <div class="flex items-center justify-between px-6 py-4">
                        <dt class="text-sm text-gray-500">Last updated</dt>
                        <dd class="text-sm tabular-nums text-gray-900">{{ $subscription->updated_at->format('M j, Y g:ia T') }}</dd>
                    </div>
                    <div class="px-6 py-4">
                        <dt class="text-sm text-gray-500 mb-1.5">Purchase token</dt>
                        <dd class="font-mono text-xs text-gray-500 break-all">{{ Str::limit($subscription->purchase_token, 80) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Raw payload (collapsed) --}}
            @if($subscription->raw_payload)
                <div x-data="{ open: false }" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <button @click="open = !open"
                        class="flex w-full items-center justify-between px-6 py-4 text-sm font-semibold text-gray-900 hover:bg-gray-50/70 transition-colors">
                        Raw payload
                        <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-gray-100 px-6 py-4">
                        <pre class="overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-600 leading-relaxed">{{ json_encode($subscription->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif

        </div>

        {{-- Right: User + Actions --}}
        <div class="space-y-6">

            {{-- User card --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Subscriber</h3>
                </div>
                @if($subscription->user)
                    @php
                        $colors = ['from-violet-500 to-purple-600','from-blue-500 to-cyan-600','from-emerald-500 to-teal-600','from-orange-400 to-rose-500','from-pink-500 to-fuchsia-600','from-amber-400 to-orange-500'];
                        $avatarGradient = $colors[$subscription->user->id % count($colors)];
                    @endphp
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex items-center gap-3.5">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br {{ $avatarGradient }} text-sm font-bold text-white shadow-sm">
                                {{ $subscription->user->initials }}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">{{ $subscription->user->name }}</div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $subscription->user->email }}</div>
                            </div>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Joined</dt>
                                <dd class="text-gray-900 tabular-nums">{{ $subscription->user->created_at->format('M j, Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Account</dt>
                                <dd>
                                    @if($subscription->user->is_active)
                                        <span class="text-emerald-600 font-medium">Active</span>
                                    @else
                                        <span class="text-red-600 font-medium">Suspended</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                        <a href="{{ route('admin.users.edit', $subscription->user) }}"
                            class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 active:scale-95">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit user account
                        </a>
                    </div>
                @else
                    <div class="px-6 py-5 text-sm text-gray-400">User account has been deleted.</div>
                @endif
            </div>

            {{-- Actions card --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">Admin actions</h3>
                </div>
                <div class="divide-y divide-gray-50">

                    {{-- Extend --}}
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Extend subscription</p>
                        <p class="text-xs text-gray-400 mb-3">Add days to the current expiry date. If expired or cancelled, the subscription will be reactivated.</p>
                        <form method="POST" action="{{ route('admin.subscriptions.extend', $subscription) }}">
                            @csrf
                            <div class="flex gap-2">
                                <input type="number" name="days" value="30" min="1" max="365"
                                    class="block w-24 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-gray-400 focus:outline-none focus:ring-0">
                                <span class="flex items-center text-xs text-gray-400">days</span>
                                <button type="submit"
                                    class="ml-auto inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-gray-700 active:scale-95">
                                    Extend
                                </button>
                            </div>
                            @error('days')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>

                    {{-- Cancel --}}
                    <div class="px-6 py-5">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Cancel subscription</p>
                        <p class="text-xs text-gray-400 mb-3">Immediately marks this subscription as cancelled and disables auto-renewal.</p>
                        @if($isCancellable)
                            <form method="POST" action="{{ route('admin.subscriptions.cancel', $subscription) }}"
                                onsubmit="return confirm('Cancel this subscription? The user will lose their Pro entitlement immediately.')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition-all hover:bg-red-100 active:scale-95">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancel subscription
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-400 cursor-not-allowed">
                                Already {{ $subscription->status }}
                            </span>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection
