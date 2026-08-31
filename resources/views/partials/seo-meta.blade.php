{{--
    Open Graph + Twitter card tags.

    Expects: $title, $description. Optional: $image (absolute URL), $type,
    $url (defaults to the current URL).

    Push this from a page's @push('meta') block. The plain description,
    keywords and canonical tags live in layouts/public and are overridden with
    @section('meta_description') / @section('canonical').
--}}
@php
    $ogType = $type ?? 'website';
    $ogUrl = $url ?? url()->current();
@endphp
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
@isset($image)
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endisset

<meta name="twitter:card" content="{{ isset($image) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@isset($image)
    <meta name="twitter:image" content="{{ $image }}">
@endisset
