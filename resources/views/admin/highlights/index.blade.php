@extends('layouts.admin')

@section('title', 'Manage Highlights')
@section('page-title', 'Trail Highlights')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-semibold tracking-tight">Trail Highlights</h2>
            <p class="text-sm text-muted-foreground">
                Edit and remove highlights (points of interest) across all trails. New highlights are added from the trail builder.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search and Filter Card -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.highlights.index') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                <div class="grid flex-1 gap-2">
                    <label class="text-sm font-medium leading-none">Search highlights</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" placeholder="Search by highlight or trail name..."
                               value="{{ request('search') }}"
                               class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                </div>

                <div class="grid gap-2 min-w-[180px]">
                    <label class="text-sm font-medium leading-none">Type</label>
                    <select name="feature_type"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="">All Types</option>
                        @foreach($featureTypes as $type => $label)
                            <option value="{{ $type }}" {{ request('feature_type') === $type ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-2 min-w-[180px]">
                    <label class="text-sm font-medium leading-none">Trail</label>
                    <select name="trail"
                            class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                        <option value="">All Trails</option>
                        @foreach($trails as $trail)
                            <option value="{{ $trail->id }}" {{ (string) request('trail') === (string) $trail->id ? 'selected' : '' }}>{{ $trail->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        Filter
                    </button>
                    @if(request('search') || request('feature_type') || request('trail'))
                        <a href="{{ route('admin.highlights.index') }}"
                           class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Highlights Table Card -->
    <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Highlight</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Trail</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Type</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Media</th>
                        <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($highlights as $highlight)
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <td class="p-4 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg text-lg select-none"
                                     style="background-color: {{ $highlight->color }}1A; border: 1px solid {{ $highlight->color }}33;">
                                    {{ $highlight->icon }}
                                </div>
                                <div class="font-medium leading-none">{{ $highlight->name }}</div>
                            </div>
                        </td>
                        <td class="p-4 align-middle">
                            @if($highlight->trail)
                                <a href="{{ route('admin.trails.edit', $highlight->trail) }}" class="text-sm hover:underline">{{ $highlight->trail->name }}</a>
                            @else
                                <span class="text-xs text-gray-400 italic">Unassigned</span>
                            @endif
                        </td>
                        <td class="p-4 align-middle">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-secondary text-secondary-foreground">
                                {{ $highlight->feature_type_label }}
                            </span>
                        </td>
                        <td class="p-4 align-middle">
                            <span class="text-sm text-muted-foreground">
                                {{ $highlight->media_count }} {{ Str::plural('item', $highlight->media_count) }}
                            </span>
                        </td>
                        <td class="p-4 align-middle">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.highlights.edit', $highlight) }}"
                                   class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 w-9"
                                   title="Edit highlight">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.highlights.destroy', $highlight) }}"
                                      onsubmit="return confirm('Delete the highlight &quot;{{ $highlight->name }}&quot;? This cannot be undone.');"
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-destructive hover:text-destructive-foreground h-9 w-9"
                                            title="Delete highlight">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="rounded-full bg-muted p-3">
                                    <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-lg font-semibold">No highlights found</h3>
                                    <p class="text-sm text-muted-foreground">Highlights are added from the trail builder when creating or editing a trail.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($highlights->hasPages())
            <div class="flex items-center justify-between border-t px-6 py-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ $highlights->firstItem() }} to {{ $highlights->lastItem() }} of {{ $highlights->total() }} results
                </div>
                <div>
                    {{ $highlights->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --primary: 221.2 83.2% 53.3%;
  --primary-foreground: 210 40% 98%;
  --secondary: 210 40% 96%;
  --secondary-foreground: 222.2 84% 4.9%;
  --muted: 210 40% 96%;
  --muted-foreground: 215.4 16.3% 46.9%;
  --accent: 210 40% 96%;
  --accent-foreground: 222.2 84% 4.9%;
  --destructive: 0 84.2% 60.2%;
  --destructive-foreground: 210 40% 98%;
  --border: 214.3 31.8% 91.4%;
  --input: 214.3 31.8% 91.4%;
  --ring: 221.2 83.2% 53.3%;
}
.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.hover\:bg-primary\/90:hover { background-color: hsl(var(--primary) / 0.9); }
.bg-secondary { background-color: hsl(var(--secondary)); }
.text-secondary-foreground { color: hsl(var(--secondary-foreground)); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-muted\/50 { background-color: hsl(var(--muted) / 0.5); }
.hover\:bg-muted\/50:hover { background-color: hsl(var(--muted) / 0.5); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.hover\:bg-destructive:hover { background-color: hsl(var(--destructive)); }
.hover\:text-destructive-foreground:hover { color: hsl(var(--destructive-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }
</style>
@endsection
