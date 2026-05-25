{{--
    Sponsor banner pinned to the bottom of the viewport, full-width.
    Required: $network (TrailNetwork). Renders nothing when no active sponsors.

    Branding:
      - Forest gradient background (#2C5F5D → #1a2e2e), matching the site footer
      - Warm orange accent stripe (#E87B35), pulling from the brand accent palette
      - Sand/cream tile under the logo (#F5F1E8) so partner marks pop on the dark surface
      - Playfair Display serif for the sponsor name to match site display typography
      - Inter sans-serif for supporting text

    Styles are scoped under .sponsor-bnr-* so this partial never depends on the
    Tailwind compiled bundle.
--}}
@php
    $activeSponsors = $network?->activeSponsors ?? collect();
@endphp

@if($activeSponsors->count() > 0)
    <div class="sponsor-bnr-stack" role="region" aria-label="Sponsor messages">
        @foreach($activeSponsors as $sponsor)
            @php
                $bannerId = 'sponsor-banner-'.$sponsor->id;
                $hasUrl = ! empty($sponsor->url);
            @endphp
            <div id="{{ $bannerId }}" data-sponsor-banner="{{ $sponsor->id }}" class="sponsor-bnr">
                <span class="sponsor-bnr__accent" aria-hidden="true"></span>
                <span class="sponsor-bnr__topo" aria-hidden="true"></span>

                <div class="sponsor-bnr__inner">
                    <div class="sponsor-bnr__logo">
                        <img src="{{ $sponsor->logoUrl() }}" alt="{{ $sponsor->name }} logo">
                    </div>

                    <div class="sponsor-bnr__copy">
                        <p class="sponsor-bnr__eyebrow">
                            <svg class="sponsor-bnr__eyebrow-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 1.5l2.39 5.46 5.93.51-4.49 3.92 1.36 5.81L10 14.27l-5.19 2.93 1.36-5.81L1.68 7.47l5.93-.51L10 1.5z"/>
                            </svg>
                            <span>{{ $sponsor->banner_text ?: 'Proudly sponsored by' }}</span>
                        </p>
                        <p class="sponsor-bnr__name">
                            {{ $sponsor->name }}@if($sponsor->tagline)<span class="sponsor-bnr__tagline"> · {{ $sponsor->tagline }}</span>@endif
                        </p>
                    </div>

                    @if($hasUrl)
                        <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="sponsor-bnr__cta">
                            <span class="sponsor-bnr__cta-text">{{ $sponsor->cta_text ?: 'Visit' }}</span>
                            <svg class="sponsor-bnr__cta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    @endif
                </div>

                <button type="button"
                        class="sponsor-bnr__close"
                        aria-label="Dismiss sponsor message"
                        onclick="document.getElementById('{{ $bannerId }}').style.display='none';">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endforeach
    </div>

    <style>
        .sponsor-bnr-stack {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9000;
            display: flex;
            flex-direction: column;
            pointer-events: none;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
        }
        .sponsor-bnr {
            position: relative;
            background: linear-gradient(135deg, #2C5F5D 0%, #1a2e2e 100%);
            color: #F5F1E8;
            box-shadow: 0 -12px 32px -12px rgba(0, 0, 0, 0.4);
            pointer-events: auto;
            overflow: hidden;
            animation: sponsorBannerSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .sponsor-bnr__accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, #E87B35 0%, #fb923c 50%, #E87B35 100%);
            display: block;
            z-index: 2;
        }
        /* Subtle topographic / mountain texture using SVG triangle pattern */
        .sponsor-bnr__topo {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'><path d='M40 8 L72 64 H8 Z' fill='none' stroke='%23F5F1E8' stroke-width='0.6' opacity='0.07'/></svg>");
            background-size: 80px 80px;
            background-repeat: repeat;
            pointer-events: none;
            opacity: 0.9;
        }
        .sponsor-bnr__inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 14px;
            max-width: 1152px;
            margin: 0 auto;
            padding: 12px 48px 12px 14px;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__inner { padding: 14px 60px 14px 18px; gap: 16px; }
        }
        .sponsor-bnr__logo {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #F5F1E8;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2), inset 0 0 0 1px rgba(232, 123, 53, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__logo { width: 52px; height: 52px; border-radius: 12px; }
        }
        .sponsor-bnr__logo img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__logo img { width: 40px; height: 40px; }
        }
        .sponsor-bnr__copy {
            min-width: 0;
            flex: 1;
        }
        .sponsor-bnr__eyebrow {
            margin: 0 0 2px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #fdba74;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__eyebrow { font-size: 12px; }
        }
        .sponsor-bnr__eyebrow-icon {
            width: 11px;
            height: 11px;
            flex-shrink: 0;
        }
        .sponsor-bnr__name {
            margin: 0;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.01em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__name { font-size: 19px; }
        }
        .sponsor-bnr__tagline {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-weight: 400;
            font-size: 13px;
            color: rgba(245, 241, 232, 0.7);
            letter-spacing: 0;
        }
        @media (min-width: 640px) {
            .sponsor-bnr__tagline { font-size: 14px; }
        }
        .sponsor-bnr__cta {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #ffffff;
            text-decoration: none;
            background: linear-gradient(135deg, #E87B35 0%, #c2410c 100%);
            box-shadow: 0 4px 12px -2px rgba(232, 123, 53, 0.5);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .sponsor-bnr__cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px -2px rgba(232, 123, 53, 0.6);
        }
        .sponsor-bnr__cta-icon { width: 13px; height: 13px; }
        .sponsor-bnr__cta-text { display: none; }
        @media (min-width: 640px) {
            .sponsor-bnr__cta { padding: 10px 16px; font-size: 13px; gap: 8px; }
            .sponsor-bnr__cta-text { display: inline; }
            .sponsor-bnr__cta-icon { width: 14px; height: 14px; }
        }
        .sponsor-bnr__close {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            z-index: 2;
            padding: 6px;
            background: rgba(245, 241, 232, 0.08);
            border: 0;
            border-radius: 8px;
            color: rgba(245, 241, 232, 0.7);
            cursor: pointer;
            transition: color 0.15s ease, background-color 0.15s ease;
        }
        .sponsor-bnr__close:hover {
            color: #ffffff;
            background: rgba(245, 241, 232, 0.16);
        }
        .sponsor-bnr__close svg { width: 16px; height: 16px; display: block; }

        @keyframes sponsorBannerSlideUp {
            from { opacity: 0; transform: translateY(100%); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            .sponsor-bnr { animation: none; }
        }
    </style>
@endif
