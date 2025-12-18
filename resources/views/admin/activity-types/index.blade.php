@extends('layouts.admin')

@section('title', 'Activity Types')
@section('page-title', 'Activity Types')

@section('content')
<div class="space-y-6">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h2 class="text-2xl font-bold tracking-tight">Activity Types</h2>
            <p class="text-muted-foreground">Manage outdoor activities available for trails</p>
        </div>
        <a href="{{ route('admin.activity-types.create') }}" 
           class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Activity Type
        </a>
    </div>

    <!-- Filters Card -->
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.activity-types.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium mb-2 block">Search</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name or description..."
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    </div>

                    <!-- Season Filter -->
                    <div>
                        <label class="text-sm font-medium mb-2 block">Season</label>
                        <select name="season" 
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="">All Seasons</option>
                            <option value="summer" {{ request('season') == 'summer' ? 'selected' : '' }}>Summer</option>
                            <option value="winter" {{ request('season') == 'winter' ? 'selected' : '' }}>Winter</option>
                            <option value="both" {{ request('season') == 'both' ? 'selected' : '' }}>All Year</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="text-sm font-medium mb-2 block">Status</label>
                        <select name="status" 
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" 
                            class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2 text-sm font-medium">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'season', 'status']))
                        <a href="{{ route('admin.activity-types.index') }}" 
                           class="inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-9 px-4 py-2 text-sm font-medium">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Activity Types Grid -->
    @if($activityTypes->isEmpty())
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">No activity types found</h3>
                <p class="text-muted-foreground mb-4">
                    @if(request()->hasAny(['search', 'season', 'status']))
                        Try adjusting your filters or search terms.
                    @else
                        Get started by creating your first activity type.
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'season', 'status']))
                    <a href="{{ route('admin.activity-types.create') }}" 
                       class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Activity Type
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($activityTypes as $activity)
                <div class="rounded-lg border bg-card shadow-sm hover:shadow-md transition-shadow" 
                     style="border-left: 4px solid {{ $activity->color }};">
                    <div class="p-6">
                        <!-- Header with Icon and Actions -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                                     style="background-color: {{ $activity->color }}20;">
                                    {{ $activity->icon }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg">{{ $activity->name }}</h3>
                                    <p class="text-sm text-muted-foreground">{{ $activity->slug }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($activity->description)
                            <p class="text-sm text-muted-foreground mb-4 line-clamp-2">
                                {{ $activity->description }}
                            </p>
                        @endif

                        <!-- Badges -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <!-- Season Badge -->
                            @php
                                $seasonColors = [
                                    'summer' => 'bg-orange-100 text-orange-700',
                                    'winter' => 'bg-blue-100 text-blue-700',
                                    'both' => 'bg-purple-100 text-purple-700',
                                ];
                                $seasonLabels = [
                                    'summer' => 'Summer',
                                    'winter' => 'Winter',
                                    'both' => 'All Year',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $seasonColors[$activity->season_applicable] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $seasonLabels[$activity->season_applicable] ?? $activity->season_applicable }}
                            </span>

                            <!-- Status Badge -->
                            @if($activity->is_active)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    Inactive
                                </span>
                            @endif

                            <!-- Trail Count Badge -->
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $activity->trails_count }} {{ Str::plural('trail', $activity->trails_count) }}
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-4 border-t">
                            <a href="{{ route('admin.activity-types.edit', $activity) }}" 
                               class="flex-1 inline-flex items-center justify-center rounded-md border border-input bg-background hover:bg-accent h-9 px-3 text-sm font-medium">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('admin.activity-types.destroy', $activity) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this activity type?{{ $activity->trails_count > 0 ? ' It is used in ' . $activity->trails_count . ' trail(s).' : '' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 h-9 px-3 text-sm font-medium"
                                        {{ $activity->trails_count > 0 ? 'disabled' : '' }}>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($activityTypes->hasPages())
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg">
                <div class="flex flex-1 justify-between sm:hidden">
                    @if ($activityTypes->onFirstPage())
                        <span class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-400">
                            Previous
                        </span>
                    @else
                        <a href="{{ $activityTypes->previousPageUrl() }}" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Previous
                        </a>
                    @endif

                    @if ($activityTypes->hasMorePages())
                        <a href="{{ $activityTypes->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Next
                        </a>
                    @else
                        <span class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-400">
                            Next
                        </span>
                    @endif
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ $activityTypes->firstItem() }}</span>
                            to
                            <span class="font-medium">{{ $activityTypes->lastItem() }}</span>
                            of
                            <span class="font-medium">{{ $activityTypes->total() }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        {{ $activityTypes->links() }}
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
/* shadcn/ui inspired color variables */
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --card: 0 0% 100%;
  --card-foreground: 222.2 84% 4.9%;
  --popover: 0 0% 100%;
  --popover-foreground: 222.2 84% 4.9%;
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
  --radius: 0.5rem;
}

.dark {
  --background: 222.2 84% 4.9%;
  --foreground: 210 40% 98%;
  --card: 222.2 84% 4.9%;
  --card-foreground: 210 40% 98%;
  --popover: 222.2 84% 4.9%;
  --popover-foreground: 210 40% 98%;
  --primary: 217.2 91.2% 59.8%;
  --primary-foreground: 222.2 84% 4.9%;
  --secondary: 217.2 32.6% 17.5%;
  --secondary-foreground: 210 40% 98%;
  --muted: 217.2 32.6% 17.5%;
  --muted-foreground: 215 20.2% 65.1%;
  --accent: 217.2 32.6% 17.5%;
  --accent-foreground: 210 40% 98%;
  --destructive: 0 62.8% 30.6%;
  --destructive-foreground: 210 40% 98%;
  --border: 217.2 32.6% 17.5%;
  --input: 217.2 32.6% 17.5%;
  --ring: 224.3 76.3% 94.1%;
}

/* Apply the color variables */
.bg-background { background-color: hsl(var(--background)); }
.text-foreground { color: hsl(var(--foreground)); }
.bg-card { background-color: hsl(var(--card)); }
.text-card-foreground { color: hsl(var(--card-foreground)); }
.bg-popover { background-color: hsl(var(--popover)); }
.text-popover-foreground { color: hsl(var(--popover-foreground)); }
.bg-primary { background-color: hsl(var(--primary)); }
.text-primary-foreground { color: hsl(var(--primary-foreground)); }
.bg-primary\/90 { background-color: hsl(var(--primary) / 0.9); }
.bg-secondary { background-color: hsl(var(--secondary)); }
.text-secondary-foreground { color: hsl(var(--secondary-foreground)); }
.bg-secondary\/80 { background-color: hsl(var(--secondary) / 0.8); }
.bg-muted { background-color: hsl(var(--muted)); }
.text-muted-foreground { color: hsl(var(--muted-foreground)); }
.bg-muted\/50 { background-color: hsl(var(--muted) / 0.5); }
.bg-accent { background-color: hsl(var(--accent)); }
.text-accent-foreground { color: hsl(var(--accent-foreground)); }
.hover\:bg-accent:hover { background-color: hsl(var(--accent)); }
.hover\:text-accent-foreground:hover { color: hsl(var(--accent-foreground)); }
.border-border { border-color: hsl(var(--border)); }
.border-input { border-color: hsl(var(--input)); }
.ring-ring { --tw-ring-color: hsl(var(--ring)); }
.ring-offset-background { --tw-ring-offset-color: hsl(var(--background)); }
</style>
@endsection