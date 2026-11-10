{{-- Expects $documents (LegalDocumentService::reviewPayload) and an optional $cancellable. --}}
<div class="flex min-h-0 flex-col gap-4"
     x-data="legalReview({{ count($documents) }})"
     x-init="$nextTick(() => checkScrolledToEnd())"
     @legal-review-open.window="show(0)">
    @foreach ($documents as $document)
        <div class="flex min-h-0 flex-col gap-3" x-show="step === {{ $loop->index }}" @unless ($loop->first) x-cloak @endunless>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Document {{ $loop->iteration }} of {{ $loop->count }}</p>
                <h2 class="text-lg font-bold text-gray-900">{{ $document['title'] }}</h2>
                <p class="text-xs text-gray-500">Version {{ $document['version'] }}, updated {{ $document['published_on'] }}</p>
            </div>

            @if ($document['is_material'] && $document['change_summary'])
                <p class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-900"><strong>What changed:</strong> {{ $document['change_summary'] }}</p>
            @endif

            <div class="legal-review-panel legal-content rounded-xl border border-gray-200 p-4 text-sm"
                 tabindex="0"
                 x-ref="panel{{ $loop->index }}"
                 @scroll.passive="checkScrolledToEnd()">{!! $document['html'] !!}</div>

            <p class="text-xs font-medium text-amber-700" x-show="! read[{{ $loop->index }}]">Scroll to the end of this document to continue.</p>

            <label class="flex items-start gap-2.5 text-sm text-gray-700" :class="read[{{ $loop->index }}] ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'">
                <input type="checkbox" x-model="agreed[{{ $loop->index }}]" :disabled="! read[{{ $loop->index }}]" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600">
                <span>I have read and agree to the {{ $document['title'] }}.</span>
            </label>
        </div>
    @endforeach

    <div class="flex items-center justify-between gap-3">
        <div>
            <button type="button" x-show="step > 0" x-cloak @click="show(step - 1)" class="text-sm font-medium text-gray-600 hover:text-gray-900">Back</button>
            @if ($cancellable ?? false)
                <button type="button" x-show="step === 0" @click="window.dispatchEvent(new CustomEvent('legal-review-cancel'))" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</button>
            @endif
        </div>
        <button type="button" @click="continueReview()" :disabled="! canContinue"
                class="rounded-xl bg-emerald-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50"
                x-text="isLastStep ? 'I Agree' : 'Next'">Next</button>
    </div>
</div>
