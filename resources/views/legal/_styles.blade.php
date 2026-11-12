<style>
    .legal-content { counter-reset: legal-section; color: #4b5563; line-height: 1.625; }
    .legal-content > * + * { margin-top: 1rem; }
    .legal-content h2 {
        counter-increment: legal-section;
        display: flex; align-items: center; gap: .5rem;
        margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #f3f4f6;
        font-size: 1.25rem; line-height: 1.75rem; font-weight: 700; color: #111827;
    }
    .legal-content h2::before {
        content: counter(legal-section);
        display: flex; flex-shrink: 0; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 9999px;
        background: #d1fae5; color: #047857; font-size: .875rem; font-weight: 700;
    }
    .legal-content h3 { padding-top: .5rem; font-size: 1rem; font-weight: 600; color: #111827; }
    .legal-content a { color: #047857; }
    .legal-content a:hover { text-decoration: underline; }
    .legal-content ul, .legal-content ol { display: flex; flex-direction: column; gap: .75rem; padding: 0; list-style: none; }
    .legal-content li { position: relative; padding-left: 1.25rem; }
    .legal-content li::before {
        content: ''; position: absolute; left: 0; top: .5rem;
        width: .5rem; height: .5rem; border-radius: 9999px; background: #10b981;
    }
    .legal-review-panel { height: min(55vh, calc(100dvh - 19rem)); min-height: 10rem; overflow-y: auto; -webkit-overflow-scrolling: touch; }
    .legal-content ol { counter-reset: legal-list; }
    .legal-content ol > li { counter-increment: legal-list; padding-left: 1.75rem; }
    .legal-content ol > li::before {
        content: counter(legal-list) '.';
        top: 0; width: auto; height: auto; border-radius: 0;
        background: none; color: #047857; font-weight: 600;
    }
    .legal-content table { width: 100%; font-size: .875rem; border: 1px solid #e5e7eb; border-collapse: collapse; }
    .legal-content th { background: #f9fafb; color: #374151; font-weight: 500; text-align: left; padding: .75rem 1rem; }
    .legal-content td { padding: .75rem 1rem; border-top: 1px solid #f3f4f6; }
    .legal-content td:first-child { font-weight: 500; }
</style>
