{{-- Festive look for Christmas-themed events, layered on top of any layout. Pushed by _head. --}}
<style>
    .event-page[data-theme="christmas"] {
        --xmas-evergreen: #0F3D2E;
        --xmas-gold: #D4A017;
        --xmas-cream: #FBF8F1;
        --xmas-line: #EADFC8;
    }

    /* Snow-cream surfaces with a faint snowflake pattern; evergreen dark bands. */
    .event-page[data-theme="christmas"].event-surface,
    .event-page[data-theme="christmas"] .event-band,
    .event-page[data-theme="christmas"] .xmas-signoff {
        background-color: var(--xmas-cream);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Cg fill='none' stroke='%230F3D2E' stroke-opacity='0.08' stroke-width='1.3' stroke-linecap='round'%3E%3Cpath transform='translate(18 22)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(98 92) scale(0.75)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(120 18) scale(0.5)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(40 120) scale(0.55)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3C/g%3E%3C/svg%3E");
    }
    .event-page[data-theme="christmas"] .event-hero,
    .event-page[data-theme="christmas"] .event-cta-band {
        background-color: var(--xmas-evergreen);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='0.09' stroke-width='1.3' stroke-linecap='round'%3E%3Cpath transform='translate(18 22)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(98 92) scale(0.75)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(120 18) scale(0.5)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3Cpath transform='translate(40 120) scale(0.55)' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3C/g%3E%3C/svg%3E");
    }
    .event-page[data-theme="christmas"] .event-hero-tint { background-color: rgb(15 61 46 / 0.72); }

    /* Holiday headings */
    .event-page[data-theme="christmas"] :is(h1, h2):not(.sr-only):not(.event-eyebrow) {
        font-family: 'Mountains of Christmas', 'Playfair Display', Georgia, serif;
        font-weight: 700;
        letter-spacing: 0.01em;
    }
    .event-page[data-theme="christmas"] h1.text-forest-900 { color: var(--tour-primary); }
    .event-page[data-theme="christmas"] :is(h2, h3).text-forest-900 { color: var(--xmas-evergreen); }

    /* Snowflake before every section label */
    .event-page[data-theme="christmas"] .event-eyebrow::before {
        content: '';
        display: inline-block;
        width: 1.05em;
        height: 1.05em;
        margin-right: 0.45em;
        vertical-align: -0.18em;
        background-color: currentColor;
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3E%3Cpath fill='none' stroke='black' stroke-width='2' stroke-linecap='round' d='M10 1.5v17M2.6 5.75l14.8 8.5M2.6 14.25l14.8-8.5M10 1.5l-2.2 2.2M10 1.5l2.2 2.2M10 18.5l-2.2-2.2M10 18.5l2.2-2.2'/%3E%3C/svg%3E") center / contain no-repeat;
    }

    /* Gold details */
    .event-page[data-theme="christmas"] .event-featured-badge { background-color: var(--xmas-gold); color: var(--xmas-evergreen); }
    .event-page[data-theme="christmas"] .event-cta-eyebrow { color: var(--xmas-gold); }
    .event-page[data-theme="christmas"] .event-map-frame { border-color: var(--xmas-gold); }
    .event-page[data-theme="christmas"] .stop-number-badge { border-color: #fff; box-shadow: 0 0 0 3px var(--xmas-gold); }
    .event-page[data-theme="christmas"] .stop-card:not(:hover):not(.active) { border-color: var(--xmas-line); }
    .event-page[data-theme="christmas"] .event-caption { background-color: rgb(15 61 46 / 0.85); }

    /* Snowfall over the cover photo */
    .xmas-snow {
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cg fill='white' fill-opacity='0.85'%3E%3Ccircle cx='20' cy='30' r='2'/%3E%3Ccircle cx='120' cy='80' r='1.6'/%3E%3Ccircle cx='60' cy='140' r='2.6'/%3E%3Ccircle cx='170' cy='170' r='1.8'/%3E%3Ccircle cx='90' cy='12' r='2.2'/%3E%3Ccircle cx='200' cy='110' r='1.4'/%3E%3Ccircle cx='150' cy='20' r='1.2'/%3E%3Ccircle cx='35' cy='190' r='1.6'/%3E%3C/g%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='340' height='340'%3E%3Cg fill='white' fill-opacity='0.85'%3E%3Ccircle cx='50' cy='60' r='3'/%3E%3Ccircle cx='260' cy='40' r='2.4'/%3E%3Ccircle cx='180' cy='200' r='3.2'/%3E%3Ccircle cx='300' cy='280' r='2.6'/%3E%3Ccircle cx='90' cy='300' r='2.2'/%3E%3Ccircle cx='220' cy='130' r='1.8'/%3E%3C/g%3E%3C/svg%3E");
        background-size: 220px 220px, 340px 340px;
        animation: xmas-snowfall 16s linear infinite;
    }
    @keyframes xmas-snowfall {
        from { background-position: 0 0, 0 0; }
        to { background-position: 0 220px, 0 340px; }
    }

    /* Twinkling string of lights */
    .xmas-bulbs-a { animation: xmas-twinkle 1.8s ease-in-out infinite alternate; }
    .xmas-bulbs-b { animation: xmas-twinkle 1.8s ease-in-out -0.9s infinite alternate; }
    @keyframes xmas-twinkle {
        from { opacity: 1; }
        to { opacity: 0.45; }
    }

    @media (prefers-reduced-motion: reduce) {
        .xmas-snow, .xmas-bulbs-a, .xmas-bulbs-b { animation: none; }
    }
</style>
