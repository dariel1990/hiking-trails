@extends('layouts.public')

@section('title', 'Privacy Policy — Xplore Smithers Trail Finder')

@push('meta')
    <meta name="description" content="Privacy Policy for Xplore Smithers Trail Finder. Learn how we collect, use, and protect your information.">
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 text-white py-16 px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl font-bold mb-3 text-white">Privacy Policy</h1>
            <p class="text-emerald-300 text-sm">Last updated: {{ date('F j, Y') }}</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-3xl mx-auto px-4 py-14">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">

            {{-- Intro --}}
            <div class="px-8 py-8">
                <p class="text-gray-600 leading-relaxed">
                    Welcome to <strong>Xplore Smithers Trail Finder</strong> ("we", "us", or "our"), a trail discovery
                    platform serving the Smithers, British Columbia area. We are committed to protecting your privacy
                    and being transparent about how information is handled when you use our website at
                    <strong>trails.xploresmithers.com</strong>. This Privacy Policy explains what information we collect,
                    how we use it, and your rights in relation to it.
                </p>
            </div>

            {{-- Section 1 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">1</span>
                    Information We Collect
                </h2>
                <p class="text-gray-600 leading-relaxed">We collect the following types of information:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Usage Data.</strong> When you browse the site we automatically collect standard
                        server log data, including your IP address, browser type, pages visited, time spent on pages,
                        and referring URLs. This is collected via Google Analytics (see Section 4).</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Newsletter Sign-up.</strong> If you subscribe to trail updates via the footer
                        form, we collect your email address. We use it only to send you relevant updates about trails
                        and local outdoor news. You can unsubscribe at any time.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Map Interactions.</strong> When you use the interactive trail map, your
                        approximate location may be requested by your browser to centre the map. This is processed
                        locally in your browser and is never sent to our servers.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Admin Accounts.</strong> If you have an administrator account, we store your
                        name, email address, and a securely hashed password. This information is used solely to
                        authenticate your access to the admin panel.</span>
                    </li>
                </ul>
            </div>

            {{-- Section 2 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">2</span>
                    How We Use Your Information
                </h2>
                <p class="text-gray-600 leading-relaxed">We use the information we collect to:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Provide, operate, and improve the Trail Finder platform and interactive map.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Send trail update newsletters to subscribers who have opted in.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Understand how visitors use the site so we can improve the experience and add new trails.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Authenticate administrators who manage trail, facility, and media content.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Comply with legal obligations where applicable.</span>
                    </li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    We do <strong>not</strong> sell, rent, or trade your personal information to third parties for
                    marketing purposes.
                </p>
            </div>

            {{-- Section 3 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">3</span>
                    Trail Content & User-Submitted Media
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Trail descriptions, GPX route files, photos, and highlight information are managed by our
                    administrative team. Photos uploaded to trails or facilities are stored on our servers and
                    displayed publicly as part of the trail listing. If you believe any content infringes on your
                    rights, please contact us using the details in Section 8.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    GPS coordinates derived from GPX files are stored to render trail routes on the interactive map.
                    No personal identifiers are extracted from GPX data — only geographic coordinates and elevation
                    information are retained.
                </p>
            </div>

            {{-- Section 4 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">4</span>
                    Cookies & Analytics
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We use <strong>Google Analytics</strong> to understand site traffic and usage patterns.
                    Google Analytics uses cookies — small text files stored on your device — to collect anonymised
                    data about how visitors interact with the site. This data is processed by Google in accordance
                    with their Privacy Policy.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    We also use session cookies to keep administrator accounts logged in. These cookies are essential
                    for the admin panel to function and are not used for tracking.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    You can control or disable cookies through your browser settings. Disabling cookies may affect
                    some functionality of the site.
                </p>
            </div>

            {{-- Section 5 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">5</span>
                    Third-Party Services
                </h2>
                <p class="text-gray-600 leading-relaxed">Our site integrates with the following third-party services:</p>
                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="w-full text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 font-medium">
                            <tr>
                                <th class="px-4 py-3 text-left">Service</th>
                                <th class="px-4 py-3 text-left">Purpose</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3 font-medium">Google Analytics</td>
                                <td class="px-4 py-3">Site traffic and usage analytics</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">OpenStreetMap / Leaflet</td>
                                <td class="px-4 py-3">Interactive trail map tiles</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">OpenRouteService</td>
                                <td class="px-4 py-3">Route calculation for trail builders</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">OpenTopoData</td>
                                <td class="px-4 py-3">Elevation profile data</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">YouTube / Vimeo</td>
                                <td class="px-4 py-3">Embedded trail videos (where provided)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium">Nominatim (OpenStreetMap)</td>
                                <td class="px-4 py-3">Location search on the map</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-gray-600 leading-relaxed">
                    Each of these services operates under its own privacy policy. We encourage you to review them
                    if you have concerns about data processed by those providers.
                </p>
            </div>

            {{-- Section 6 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">6</span>
                    Data Retention
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We retain personal data only as long as necessary for the purposes described in this policy:
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Newsletter subscribers:</strong> until you unsubscribe.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Admin accounts:</strong> for the duration of the account's active status.
                        Deleted accounts are permanently removed from our database.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Analytics data:</strong> subject to Google Analytics' retention settings
                        (typically 26 months).</span>
                    </li>
                </ul>
            </div>

            {{-- Section 7 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">7</span>
                    Your Rights
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Depending on your location, you may have the following rights regarding your personal data:
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Access:</strong> request a copy of the personal data we hold about you.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Correction:</strong> ask us to correct inaccurate or incomplete data.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Deletion:</strong> request that we delete your personal data, subject to any
                        legal obligations to retain it.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span><strong>Unsubscribe:</strong> opt out of newsletter emails at any time using the
                        unsubscribe link in any email we send.</span>
                    </li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    To exercise any of these rights, please contact us using the details below.
                </p>
            </div>

            {{-- Section 8 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">8</span>
                    Children's Privacy
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Our site is intended for general audiences and is not directed at children under the age of 13.
                    We do not knowingly collect personal information from children. If you believe a child has
                    provided us with personal information, please contact us and we will promptly delete it.
                </p>
            </div>

            {{-- Section 9 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">9</span>
                    Changes to This Policy
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We may update this Privacy Policy from time to time. When we do, we will revise the
                    "Last updated" date at the top of this page. We encourage you to review this policy
                    periodically. Continued use of the site after changes are posted constitutes your
                    acceptance of the updated policy.
                </p>
            </div>

            {{-- Section 10 - Contact --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">10</span>
                    Contact Us
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    If you have any questions, concerns, or requests regarding this Privacy Policy or the data
                    we hold, please reach out to us:
                </p>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-6 space-y-2 text-gray-700">
                    <p class="font-semibold text-emerald-800">Xplore Smithers</p>
                    <p class="text-sm">Smithers, British Columbia, Canada</p>
                    <a href="https://xploresmithers.com" target="_blank"
                        class="text-sm text-emerald-700 hover:underline block">xploresmithers.com</a>
                </div>
            </div>

        </div>

        {{-- Back link --}}
        <div class="mt-8 text-center">
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-2 text-sm text-emerald-700 hover:text-emerald-900 font-medium transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
