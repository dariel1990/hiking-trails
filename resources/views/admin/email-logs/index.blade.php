@extends('layouts.admin')

@section('title', 'Email Logs')
@section('page-title', 'Email Logs')

@section('content')
<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">Management</p>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900" style="font-family: 'Inter', sans-serif;">Email Logs</h2>
            <p class="mt-1 text-sm text-gray-500">Every notification email the app has attempted to send. Resend any failed email instantly.</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

        {{-- Total --}}
        <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100">
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-gray-700">{{ $stats['total'] }}</p>
            <p class="mt-1 text-sm text-gray-500">emails attempted</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-gray-400"></div>
        </div>

        {{-- Sent --}}
        <div class="relative overflow-hidden rounded-xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-400 uppercase tracking-wider">Sent</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-emerald-700">{{ $stats['sent'] }}</p>
            <p class="mt-1 text-sm text-gray-500">delivered to the mail server</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-emerald-400"></div>
        </div>

        {{-- Failed --}}
        <div class="relative overflow-hidden rounded-xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-red-400 uppercase tracking-wider">Failed</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-red-700">{{ $stats['failed'] }}</p>
            <p class="mt-1 text-sm text-gray-500">failed to send</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-red-400"></div>
        </div>

        {{-- Failed last 7 days --}}
        <div class="relative overflow-hidden rounded-xl border border-amber-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-amber-400 uppercase tracking-wider">Recent</span>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums text-amber-700">{{ $stats['failed_last_7_days'] }}</p>
            <p class="mt-1 text-sm text-gray-500">failures in the last 7 days</p>
            <div class="absolute bottom-0 left-0 h-0.5 w-full bg-amber-400"></div>
        </div>

    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Email history</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $logs->total() }} {{ Str::plural('record', $logs->total()) }} total</p>
            </div>
            <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50 p-1">
                @foreach(['' => 'All', 'sent' => 'Sent', 'failed' => 'Failed'] as $value => $label)
                    <a href="{{ route('admin.email-logs.index', $value ? ['status' => $value] : []) }}"
                        class="rounded-md px-3 py-1 text-xs font-medium transition-colors {{ $status === ($value ?: null) ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="h-11 px-6 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Recipient</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Notification</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Error</th>
                        <th class="h-11 px-4 text-left align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Date</th>
                        <th class="h-11 px-6 text-right align-middle text-xs font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                        @php
                            $statusColors = [
                                \App\Models\EmailLog::STATUS_SENT => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                \App\Models\EmailLog::STATUS_FAILED => 'bg-red-50 text-red-700 ring-red-200',
                            ];
                            $statusColor = $statusColors[$log->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                        @endphp
                        <tr class="group transition-colors hover:bg-gray-50/70">

                            {{-- Recipient --}}
                            <td class="py-4 pl-6 pr-4 align-middle">
                                <div class="font-mono text-xs text-gray-700">{{ $log->recipient_email }}</div>
                            </td>

                            {{-- Notification --}}
                            <td class="px-4 py-4 align-middle">
                                <div class="text-sm font-medium text-gray-900">{{ $log->typeLabel() }}</div>
                                @if($log->subject)
                                    <div class="mt-0.5 text-xs text-gray-400">{{ $log->subject }}</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4 align-middle">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center rounded px-2 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusColor }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    @if($log->resent_at)
                                        <span class="inline-flex items-center rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200"
                                            title="Resent {{ $log->resent_at->format('M j, Y H:i') }}">
                                            Resent
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Error --}}
                            <td class="px-4 py-4 align-middle max-w-xs">
                                @if($log->error)
                                    <div x-data="{ open: false }">
                                        <p class="text-xs text-red-600" :class="open ? '' : 'truncate'" style="max-width: 20rem;">{{ $log->error }}</p>
                                        @if(strlen($log->error) > 60)
                                            <button type="button" @click="open = !open" class="mt-0.5 text-xs font-medium text-gray-400 hover:text-gray-600">
                                                <span x-text="open ? 'Show less' : 'Show more'"></span>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-4 align-middle">
                                <span class="text-sm tabular-nums text-gray-500" title="{{ $log->created_at->format('M j, Y H:i:s') }}">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 pl-4 pr-6 align-middle">
                                <div class="flex items-center justify-end">
                                    @if($log->status === \App\Models\EmailLog::STATUS_FAILED && $log->resent_at)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600"
                                            title="Resent {{ $log->resent_at->format('M j, Y H:i') }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Resent {{ $log->resent_at->diffForHumans() }}
                                        </span>
                                    @elseif($log->status === \App\Models\EmailLog::STATUS_FAILED)
                                        <form method="POST" action="{{ route('admin.email-logs.resend', $log) }}"
                                            onsubmit="return confirm('Resend this email to {{ $log->recipient_email }}?')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 shadow-sm transition-all hover:border-emerald-300 hover:bg-emerald-100 active:scale-95">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Resend
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                                        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900">No emails logged yet</h3>
                                    <p class="mt-1 text-sm text-gray-400">Notification emails will appear here as soon as the app sends one.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
            <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-3">
                <p class="text-xs text-gray-400 tabular-nums">
                    Showing <span class="font-semibold text-gray-600">{{ $logs->firstItem() }}</span>–<span class="font-semibold text-gray-600">{{ $logs->lastItem() }}</span> of <span class="font-semibold text-gray-600">{{ $logs->total() }}</span>
                </p>
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
