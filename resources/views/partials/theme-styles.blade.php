{{-- Admin theme overrides: emits nothing at all while every theme setting
     is at its default, so the compiled Tailwind fallbacks keep the original
     hand-tuned design byte-for-byte. --}}
@php($theme = app(\App\Services\ThemeService::class))
@unless($theme->isDefault())
    @if($themeFontLink = $theme->fontLink())
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="{{ $themeFontLink }}">
    @endif
    {{-- Values are trusted: RGB triplets computed by ThemeService and font
         stacks from config/theme-fonts.php — never raw user input. --}}
    <style id="theme-overrides">:root{@foreach($theme->cssVariables() as $variable => $value){{ $variable }}:{!! $value !!};@endforeach}</style>
@endunless
