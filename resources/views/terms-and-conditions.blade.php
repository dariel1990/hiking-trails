@extends('layouts.public')

@section('title', 'Terms & Conditions — Xplore Smithers')

@push('meta')
    <meta name="description" content="Terms and Conditions for the Xplore Smithers app. Review the rules, disclaimers, and policies that govern your use of the App.">
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen">

    {{-- Hero --}}
    <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 text-white py-16 px-4">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl font-bold mb-3 text-white">Terms &amp; Conditions</h1>
            <p class="text-emerald-300 text-sm">Last updated: June 9, 2026</p>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-3xl mx-auto px-4 py-14">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">

            {{-- Intro --}}
            <div class="px-8 py-8">
                <p class="text-gray-600 leading-relaxed">
                    Welcome to <strong>Xplore Smithers</strong> ("App"), operated by Camus Photography Ltd.
                    ("Xplore Smithers", "we", "us", or "our"). By downloading, accessing, creating an account, or
                    using the App, you agree to be bound by these Terms and Conditions ("Terms"). If you do not agree
                    to these Terms, you must not use the App. Your use of the App is also governed by our
                    <a href="{{ route('privacy-policy') }}" class="text-emerald-700 hover:underline">Privacy Policy</a>.
                </p>
            </div>

            {{-- Section 1 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">1</span>
                    Agreement to Terms
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Welcome to Xplore Smithers ("App"), operated by Camus Photography Ltd. ("Xplore Smithers", "we",
                    "us", or "our").
                </p>
                <p class="text-gray-600 leading-relaxed">
                    By downloading, accessing, creating an account, or using the App, you agree to be bound by these
                    Terms and Conditions ("Terms"). If you do not agree to these Terms, you must not use the App.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Your use of the App is also governed by our Privacy Policy.
                </p>
            </div>

            {{-- Section 2 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">2</span>
                    Eligibility
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    You must be at least 13 years old to use the App.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    If you are under the age of majority in your jurisdiction, you confirm that you have permission
                    from a parent or legal guardian to use the App and agree to these Terms.
                </p>
            </div>

            {{-- Section 3 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">3</span>
                    Accounts
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Certain features require an account, including Google Sign-In.
                </p>
                <p class="text-gray-600 leading-relaxed">You agree to:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Provide accurate and current information;</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Maintain the security of your account;</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>Be responsible for all activity occurring under your account.</span>
                    </li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    We reserve the right to suspend or terminate accounts that violate these Terms.
                </p>
            </div>

            {{-- Section 4 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">4</span>
                    Subscriptions and Billing
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Certain features may require a paid subscription, including offline maps and premium content.
                </p>
                <p class="text-gray-700 font-semibold">Subscription Plans:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>$4.99 CAD per month</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>$39.99 CAD per year</span>
                    </li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    Subscriptions are processed through Google Play Billing.
                </p>
                <h3 class="text-base font-semibold text-gray-900 pt-2">Free Trial</h3>
                <p class="text-gray-600 leading-relaxed">
                    New subscribers may receive a 7-day free trial. Unless cancelled before the trial ends, the
                    subscription automatically converts to a paid subscription.
                </p>
                <h3 class="text-base font-semibold text-gray-900 pt-2">Auto-Renewal</h3>
                <p class="text-gray-600 leading-relaxed">
                    Subscriptions automatically renew unless cancelled through Google Play before the renewal date.
                </p>
                <h3 class="text-base font-semibold text-gray-900 pt-2">Refunds</h3>
                <p class="text-gray-600 leading-relaxed">
                    Refund requests are handled by Google Play according to their policies.
                </p>
                <h3 class="text-base font-semibold text-gray-900 pt-2">Pricing</h3>
                <p class="text-gray-600 leading-relaxed">
                    We reserve the right to change pricing in the future. Any pricing changes will apply only to
                    future billing periods.
                </p>
            </div>

            {{-- Section 5 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">5</span>
                    Outdoor Activities and Assumption of Risk
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    The App provides information regarding trails, recreation sites, businesses, events, and outdoor
                    activities.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Outdoor recreation involves inherent risks, including but not limited to:
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Wildlife encounters</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Bear activity</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Changing weather conditions</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Flooding and water crossings</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Falling trees</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Rockfall and landslides</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Remote terrain</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Limited cellular service</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Personal injury or death</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    By using the App and participating in outdoor activities, you voluntarily assume all risks
                    associated with such activities.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    You are solely responsible for your own safety, preparedness, equipment, navigation, and decisions
                    while outdoors.
                </p>
            </div>

            {{-- Section 6 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">6</span>
                    Maps, GPS and Navigation Disclaimer
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Maps, GPS positioning, trail information, offline content, and navigation features may be
                    inaccurate, incomplete, unavailable, or out of date.
                </p>
                <p class="text-gray-600 leading-relaxed">The App is not a substitute for:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Proper trip planning</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Physical maps</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Emergency equipment</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Navigation training</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Sound judgment</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    GPS accuracy depends on your device, satellite coverage, terrain, weather, and other factors
                    beyond our control.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    You should always verify information independently before relying on it.
                </p>
            </div>

            {{-- Section 7 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">7</span>
                    Emergency Use Disclaimer
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    The App is not intended for emergency use.
                </p>
                <p class="text-gray-600 leading-relaxed">Do not rely on the App for:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Emergency navigation</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Emergency communications</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Rescue assistance</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Safety-critical information</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    Always carry appropriate emergency equipment and contact emergency services directly when required.
                </p>
            </div>

            {{-- Section 8 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">8</span>
                    Trail Access and Private Property
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    Trail access, road conditions, land ownership, permissions, closures, and restrictions may change
                    without notice.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    The inclusion of a trail, road, recreation site, or location within the App does not guarantee
                    public access.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Users are solely responsible for determining whether access is lawful and permitted at the time
                    of use.
                </p>
            </div>

            {{-- Section 9 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">9</span>
                    Business Listings and Third-Party Information
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    The App may contain listings, advertisements, promotions, reviews, recommendations, or information
                    about businesses, accommodations, restaurants, tour operators, events, and other third parties.
                </p>
                <p class="text-gray-600 leading-relaxed">We do not guarantee:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Accuracy</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Availability</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Pricing</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Quality</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Safety</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Suitability</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    Any transaction or interaction between you and a third party is solely between you and that third
                    party.
                </p>
            </div>

            {{-- Section 10 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">10</span>
                    License and Acceptable Use
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We grant you a limited, non-exclusive, non-transferable, revocable license to use the App for
                    personal and non-commercial purposes.
                </p>
                <p class="text-gray-600 leading-relaxed">You agree not to:</p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Copy or redistribute App content</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Reverse engineer the App</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Interfere with App operations</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Use the App unlawfully</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Commercially exploit App content without permission</span></li>
                </ul>
            </div>

            {{-- Section 11 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">11</span>
                    Intellectual Property
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    All content within the App, including software, text, graphics, logos, photographs, videos, maps,
                    trail information, branding, and design elements, is owned by Camus Photography Ltd. or its
                    licensors.
                </p>
                <p class="text-gray-600 leading-relaxed">All rights are reserved.</p>
            </div>

            {{-- Section 12 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">12</span>
                    User Content
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    If the App allows users to submit content, including routes, photos, reviews, comments, or notes,
                    you retain ownership of your content.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    However, you grant us a non-exclusive, worldwide, royalty-free license to use, display, reproduce,
                    and distribute such content for the operation and promotion of the App.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    We reserve the right to remove content that violates these Terms.
                </p>
            </div>

            {{-- Section 13 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">13</span>
                    Third-Party Services
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    The App may rely on third-party services including:
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Google Sign-In</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Google Play Billing</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Mapbox</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Analytics providers</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    Your use of these services may be subject to their own terms and privacy policies.
                </p>
            </div>

            {{-- Section 14 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">14</span>
                    Disclaimer of Warranties
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    The App is provided "AS IS" and "AS AVAILABLE."
                </p>
                <p class="text-gray-600 leading-relaxed">
                    To the fullest extent permitted by law, we disclaim all warranties, express or implied, including:
                </p>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Merchantability</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Fitness for a particular purpose</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Accuracy</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Reliability</span></li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span><span>Non-infringement</span></li>
                </ul>
                <p class="text-gray-600 leading-relaxed">
                    We do not guarantee uninterrupted, secure, or error-free operation.
                </p>
            </div>

            {{-- Section 15 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">15</span>
                    Limitation of Liability
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    To the fullest extent permitted by law, Camus Photography Ltd., its officers, directors, employees,
                    contractors, and partners shall not be liable for any indirect, incidental, special, consequential,
                    punitive, or exemplary damages arising from your use of the App.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Our maximum total liability for any claim shall not exceed the amount paid by you for the App
                    during the twelve (12) months preceding the claim.
                </p>
            </div>

            {{-- Section 16 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">16</span>
                    Termination
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We may suspend or terminate access to the App at any time if you violate these Terms.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Upon termination, your right to use the App immediately ceases.
                </p>
            </div>

            {{-- Section 17 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">17</span>
                    Changes to These Terms
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    We may update these Terms from time to time.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Material changes may be communicated through the App or our website.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Continued use of the App following any update constitutes acceptance of the revised Terms.
                </p>
            </div>

            {{-- Section 18 --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">18</span>
                    Governing Law
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    These Terms are governed by the laws of British Columbia and the laws of Canada applicable therein.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Any dispute arising from these Terms shall be subject to the exclusive jurisdiction of the courts
                    of British Columbia.
                </p>
            </div>

            {{-- Section 19 - Contact --}}
            <div class="px-8 py-8 space-y-4">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-sm font-bold">19</span>
                    Contact Us
                </h2>
                <p class="text-gray-600 leading-relaxed">
                    If you have any questions regarding these Terms, please contact:
                </p>
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-6 space-y-2 text-gray-700">
                    <a href="mailto:info@xploresmithers.com"
                        class="text-sm text-emerald-700 hover:underline block">info@xploresmithers.com</a>
                    <p class="font-semibold text-emerald-800">Xplore Smithers</p>
                    <p class="text-sm">Camus Photography Ltd.</p>
                    <p class="text-sm">Smithers, British Columbia, Canada</p>
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
