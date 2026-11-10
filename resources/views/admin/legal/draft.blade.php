@extends('layouts.admin')

@section('title', 'Edit '.$title)
@section('page-title', 'Edit '.$title)

@section('content')
@include('legal._styles')
<script>
    function legalDraftEditor(initialContent, savedContent, previewUrl) {
        return {
            content: initialContent,
            savedContent,
            previewHtml: '',
            get isDirty() {
                return this.content !== this.savedContent;
            },
            async refreshPreview() {
                const response = await fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ content: this.content }),
                });

                if (response.ok) {
                    this.previewHtml = (await response.json()).html;
                }
            },
        };
    }
</script>

<div class="space-y-6" x-data="legalDraftEditor(@js($content), @js($draft?->content), @js(route('admin.legal.preview')))" x-init="refreshPreview()">
    <div>
        <a href="{{ route('admin.legal.history', $type) }}" class="text-sm text-emerald-700 hover:underline">Version history</a>
        <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $draft ? 'Draft' : 'New version' }} of {{ $title }}</h2>
        <p class="mt-1 text-sm text-gray-500">Drafts are private. Use <code>##</code> for numbered section headings, <code>###</code> for sub-headings, and <code>-</code> for bullets.</p>
    </div>

    @include('admin.legal._flash')

    <form method="POST" action="{{ route('admin.legal.draft.save', $type) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="flex flex-col gap-2">
                <label for="content" class="text-sm font-medium text-gray-700">Markdown</label>
                <textarea id="content" name="content" rows="28" x-model="content" @input.debounce.400ms="refreshPreview()" class="w-full rounded-xl border-gray-300 font-mono text-sm shadow-sm">{{ $content }}</textarea>
            </div>
            <div class="flex flex-col gap-2">
                <p class="text-sm font-medium text-gray-700">Preview</p>
                <div class="legal-content max-h-[42rem] overflow-y-auto rounded-xl border border-gray-200 bg-white p-6 shadow-sm" x-html="previewHtml"></div>
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Save draft</button>
    </form>

    @if ($draft)
        <form method="POST" action="{{ route('admin.legal.draft.publish', $type) }}" class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900">Publish this draft</h3>

            <div class="flex flex-col gap-1">
                <label for="change_summary" class="text-sm font-medium text-gray-700">What changed?</label>
                <textarea id="change_summary" name="change_summary" rows="3" required maxlength="2000" class="w-full rounded-xl border-gray-300 text-sm shadow-sm">{{ old('change_summary') }}</textarea>
                <p class="text-xs text-gray-500">Permanent record. Also shown to users when they are asked to re-accept.</p>
            </div>

            <fieldset class="flex flex-col gap-2">
                <legend class="text-sm font-medium text-gray-700">Is this a material change?</legend>
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_material" value="1" required @checked(old('is_material') === '1') class="mt-0.5 text-emerald-600">
                    <span><strong>Yes.</strong> Every logged-in website user must accept it before continuing.</span>
                </label>
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="radio" name="is_material" value="0" required @checked(old('is_material') === '0') class="mt-0.5 text-emerald-600">
                    <span><strong>No.</strong> Typo or clarification. Goes live without asking anyone to accept.</span>
                </label>
            </fieldset>

            <div class="flex items-center gap-3">
                <button type="submit" :disabled="isDirty" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">Publish</button>
                <p x-show="isDirty" x-cloak class="text-sm text-amber-700">Save your changes before publishing.</p>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.legal.draft.discard', $type) }}" onsubmit="return confirm('Discard this draft? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Discard draft</button>
        </form>
    @endif
</div>
@endsection
