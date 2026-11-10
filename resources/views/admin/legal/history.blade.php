@extends('layouts.admin')

@section('title', $title.' history')
@section('page-title', $title.' history')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.legal.index') }}" class="text-sm text-emerald-700 hover:underline">Legal Documents</a>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $title }} history</h2>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.legal.compare', $type) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Compare versions</a>
            <a href="{{ route('admin.legal.draft', $type) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">New version</a>
        </div>
    </div>

    @include('admin.legal._flash')

    <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Version</th>
                    <th class="px-4 py-3">Published</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">What changed</th>
                    <th class="px-4 py-3">Material</th>
                    <th class="px-4 py-3">Acceptances</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($versions as $version)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">v{{ $version->version }}@if ($loop->first) <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Live</span>@endif</td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ $version->published_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $version->publisher?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $version->change_summary }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $version->is_material ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($version->acceptances_count) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.legal.version', [$type, $version->version]) }}" class="font-medium text-emerald-700 hover:underline">View</a>
                            @unless ($version->version === 1)
                                <a href="{{ route('admin.legal.compare', [$type, 'from' => $version->version - 1, 'to' => $version->version]) }}" class="ml-3 font-medium text-emerald-700 hover:underline">Diff</a>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
