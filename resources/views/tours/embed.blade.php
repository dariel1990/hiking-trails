{{-- Map-only tour or event page for embedding in an iframe (no site header/footer). Expects $title, $map, $mapboxToken, $fullPageUrl. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — Route Map</title>
    @include('tours._map-styles')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #e5e7eb;
        }
        #tour-map { position: absolute; inset: 0; }
        .embed-bar {
            position: absolute; z-index: 5; left: 10px; top: 10px; right: 56px;
            display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
            pointer-events: none;
        }
        .embed-chip, .embed-button {
            pointer-events: auto;
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255, 255, 255, 0.96); color: #111827;
            border: 1px solid rgba(0, 0, 0, 0.08); border-radius: 9999px;
            padding: 6px 12px; font-size: 12px; font-weight: 700; line-height: 1.2;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
            text-decoration: none;
        }
        .embed-chip { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .embed-chip-dot { width: 10px; height: 10px; border-radius: 9999px; background: var(--tour-primary); flex-shrink: 0; }
        .embed-button { cursor: pointer; font-family: inherit; }
        .embed-button:hover { background: #fff; color: var(--tour-primary); }
        .embed-button:focus-visible { outline: 3px solid var(--tour-accent); outline-offset: 2px; }
        .embed-actions {
            position: absolute; z-index: 5; left: 10px; bottom: 28px;
            display: flex; gap: 6px;
        }
        .embed-button-primary { background: var(--tour-primary); color: #fff; border-color: transparent; }
        .embed-button-primary:hover { background: var(--tour-primary-dark); color: #fff; }
    </style>
</head>
<body>
    <div id="tour-map" role="region" aria-label="{{ $title }} route map"></div>

    <div class="embed-bar">
        <span class="embed-chip"><span class="embed-chip-dot"></span>{{ $title }}</span>
    </div>

    <div class="embed-actions">
        <button type="button" id="tour-map-reset" class="embed-button" title="Show the whole route">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/>
            </svg>
            Reset view
        </button>
        <a href="{{ $fullPageUrl }}" target="_blank" rel="noopener" class="embed-button embed-button-primary">
            View full map
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5h5v5M19 5l-8 8M10 5H5v14h14v-5"/>
            </svg>
        </a>
    </div>

    @include('tours._map-script', ['showPopups' => true])
</body>
</html>
