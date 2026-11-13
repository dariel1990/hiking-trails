{{-- Shared map styles for the tour page and its embeddable map. Expects $tour. --}}
<link href="https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css" rel="stylesheet">
<style>
    /* Theme colours. Christmas event tours swap in the festive palette. */
    :root {
        --tour-primary: #2C5F5D;
        --tour-primary-dark: #1d4644;
        --tour-primary-soft: #f5f8f7;
        --tour-primary-border: #a8c4b9;
        --tour-accent: #e8a33d;
        --tour-accent-dark: #c9821f;
        --tour-accent-glow: rgba(232, 163, 61, 0.35);
        --tour-accent-glow-clear: rgba(232, 163, 61, 0);
    }
    @if($tour->isChristmasEvent())
    :root {
        --tour-primary: #B3202A;
        --tour-primary-dark: #7f1720;
        --tour-primary-soft: #fdf4f4;
        --tour-primary-border: #e7a9ad;
        --tour-accent: #D4A017;
        --tour-accent-dark: #a87d0f;
        --tour-accent-glow: rgba(212, 160, 23, 0.4);
        --tour-accent-glow-clear: rgba(212, 160, 23, 0);
    }
    @endif

    /* Map style switcher */
    .tour-layer-card {
        position: relative; cursor: pointer; border-radius: 0.5rem;
        overflow: hidden; border: 2px solid #E5E7EB;
        display: flex; flex-direction: column; align-items: center;
    }
    .tour-layer-card:hover { border-color: var(--tour-primary-border); }
    .tour-layer-card.active { border-color: var(--tour-primary); box-shadow: 0 0 0 3px rgba(0,0,0,0.06); }
    .tour-layer-card .layer-preview { width: 100%; height: 56px; overflow: hidden; }
    .tour-layer-card .layer-label { display: block; font-size: 0.7rem; font-weight: 600; color: #374151; text-align: center; padding: 0.25rem 0.25rem 0.35rem; }
    .tour-layer-card .layer-checkmark { position: absolute; top: 4px; right: 4px; width: 16px; height: 16px; color: white; background-color: var(--tour-primary); border-radius: 50%; padding: 2px; display: none; }
    .tour-layer-card.active .layer-checkmark { display: block; }

    /* Map markers */
    .tour-marker { cursor: pointer; }
    .tour-marker-badge {
        position: relative;
        width: 2.1rem; height: 2.1rem; border-radius: 9999px;
        background: linear-gradient(145deg, var(--tour-primary), var(--tour-primary-dark));
        color: #fff; font-size: 0.85rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.35);
        border: 2.5px solid #fff;
        flex-shrink: 0;
        transition: box-shadow 0.15s, filter 0.15s, transform 0.15s;
        z-index: 2;
    }
    .tour-marker:hover .tour-marker-badge { box-shadow: 0 4px 14px rgba(0,0,0,0.45); filter: brightness(1.15); transform: scale(1.06); }
    .tour-marker.active .tour-marker-badge {
        background: linear-gradient(145deg, var(--tour-accent), var(--tour-accent-dark));
        box-shadow: 0 0 0 4px var(--tour-accent-glow), 0 4px 14px rgba(0,0,0,0.45);
        transform: scale(1.15);
        animation: tour-marker-pulse 1.4s ease-out infinite;
    }
    .tour-marker-label {
        position: absolute; left: 1.6rem; top: 50%; transform: translateY(-50%); z-index: 1;
        padding: 0.3rem 0.7rem 0.3rem 1rem;
        background: rgba(255,255,255,0.95); color: #1f2937;
        font-size: 0.72rem; font-weight: 600; white-space: nowrap;
        border-radius: 0 9999px 9999px 0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.18);
        border: 1px solid rgba(0,0,0,0.06); border-left: none;
        transition: box-shadow 0.15s;
        display: flex; align-items: center; gap: 0.35rem;
        pointer-events: none;
    }
    /* Label tucked behind the left side of the badge */
    .tour-marker.label-left .tour-marker-label {
        padding: 0.3rem 1rem 0.3rem 0.7rem;
        border-radius: 9999px 0 0 9999px;
        border-left: 1px solid rgba(0,0,0,0.06); border-right: none;
    }
    /* Label nudged up/down beside a stacked badge: a full pill, not tucked */
    .tour-marker.label-detached .tour-marker-label {
        padding: 0.3rem 0.7rem;
        border-radius: 9999px;
        border: 1px solid rgba(0,0,0,0.06);
    }
    /* Hidden only when it would overlap another label/badge; hover or selecting brings it back */
    .tour-marker.label-hidden .tour-marker-label { visibility: hidden; }
    .tour-marker.label-hidden:hover .tour-marker-label,
    .tour-marker.label-hidden.active .tour-marker-label { visibility: visible; }
    .tour-marker:hover, .tour-marker.active { z-index: 10; }
    .tour-marker:hover .tour-marker-label { box-shadow: 0 4px 10px rgba(0,0,0,0.25); }
    .tour-marker.active .tour-marker-label {
        font-size: 0.85rem;
        box-shadow: 0 4px 10px var(--tour-accent-glow);
        border: 2px solid var(--tour-accent);
    }
    @keyframes tour-marker-pulse {
        0% { box-shadow: 0 0 0 4px var(--tour-accent-glow), 0 4px 14px rgba(0,0,0,0.45); }
        70% { box-shadow: 0 0 0 10px var(--tour-accent-glow-clear), 0 4px 14px rgba(0,0,0,0.45); }
        100% { box-shadow: 0 0 0 4px var(--tour-accent-glow-clear), 0 4px 14px rgba(0,0,0,0.45); }
    }
    /* Stops with their own icon (event activities): emoji/image on a white badge */
    .tour-marker.has-icon .tour-marker-badge {
        background: #fff;
        border: 2.5px solid var(--tour-primary);
        overflow: hidden;
    }
    .tour-marker.has-icon.active .tour-marker-badge { background: #fff; border-color: var(--tour-accent); }
    .tour-marker-badge-emoji { font-size: 1.15rem; line-height: 1; }
    .tour-marker-badge-img { width: 100%; height: 100%; object-fit: contain; padding: 2px; }
    .tour-marker-icon { width: 1.05rem; height: 1.05rem; border-radius: 9999px; object-fit: cover; flex-shrink: 0; }
    .tour-marker-icon-emoji {
        display: flex; align-items: center; justify-content: center; font-size: 0.85rem; line-height: 1;
        width: 1.05rem; height: 1.05rem; flex-shrink: 0; overflow: hidden; white-space: nowrap;
    }

    /* Route start/finish markers (Christmas event routes) */
    .route-endpoint {
        display: flex; align-items: center; gap: 0.3rem;
        padding: 0.2rem 0.55rem 0.2rem 0.3rem;
        background: #fff; color: #1f2937; border-radius: 9999px;
        font-size: 0.7rem; font-weight: 700; white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        border: 2px solid var(--tour-primary);
        pointer-events: none;
    }
    .route-endpoint-icon { font-size: 1rem; line-height: 1; }

    /* Stop popups (embedded map) */
    .tour-popup .mapboxgl-popup-content {
        padding: 0.65rem 0.8rem 0.7rem; border-radius: 0.7rem; font-family: inherit;
        max-height: 60vh; overflow-y: auto;
    }
    .tour-popup .mapboxgl-popup-close-button { font-size: 1.1rem; padding: 0 0.4rem; z-index: 1; }
    .tour-popup-photo {
        display: block; width: calc(100% + 1.6rem); margin: -0.65rem -0.8rem 0.55rem;
        max-height: 170px; object-fit: cover; border-radius: 0.7rem 0.7rem 0 0;
    }
    .tour-popup-head { display: flex; align-items: center; gap: 0.45rem; margin-right: 1rem; }
    .tour-popup-icon { font-size: 1.35rem; line-height: 1; }
    .tour-popup-icon-img { width: 1.6rem; height: 1.6rem; object-fit: contain; flex-shrink: 0; }
    .tour-popup-number {
        flex-shrink: 0; width: 1.4rem; height: 1.4rem; border-radius: 9999px;
        background: var(--tour-primary); color: #fff; font-size: 0.7rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .tour-popup h3 { font-size: 0.9rem; font-weight: 700; color: #111827; margin: 0; line-height: 1.25; }
    .tour-popup p { font-size: 0.78rem; color: #4b5563; margin: 0.3rem 0 0; line-height: 1.4; white-space: pre-line; }
    .tour-popup .tour-popup-time { color: var(--tour-primary); font-weight: 700; }
    .tour-popup .tour-popup-note { color: #374151; font-weight: 600; }
    .tour-popup-link {
        display: inline-block; margin-top: 0.45rem; font-size: 0.75rem; font-weight: 700;
        color: var(--tour-primary); text-decoration: none;
    }
    .tour-popup-link:hover { text-decoration: underline; }
</style>
