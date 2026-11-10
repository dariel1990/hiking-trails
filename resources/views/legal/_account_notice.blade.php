{{-- Account-menu notice for legal updates to accept. Expects $pendingLegalTitles and $class. --}}
<a href="{{ route('legal.accept.show') }}" class="{{ $class }}">
    <span class="relative mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center text-gray-400">
        <i class="fas fa-file-contract text-sm" aria-hidden="true"></i>
        <span class="absolute -right-1 -top-1 h-2 w-2 rounded-full bg-red-500"></span>
    </span>
    <span class="flex min-w-0 flex-col">
        <span class="font-medium text-gray-900">Review updated terms</span>
        <span class="text-xs text-gray-500">{{ collect($pendingLegalTitles)->join(', ', ' and ') }}</span>
    </span>
</a>
