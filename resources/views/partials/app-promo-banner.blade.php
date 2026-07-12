@php
    // Hide the Google Play promo when the page is opened inside the Android WebView app.
    // Android WebViews append "; wv)" to the User-Agent — Chrome/Firefox on Android don't.
    $isAndroidWebView = str_contains((string) request()->userAgent(), '; wv)');
@endphp
@if(config('services.android_app.play_store_url') && ! $isAndroidWebView)
<!-- Mobile App Promo (hero) -->
<div class="slide-in-up mb-12 flex flex-col md:flex-row items-center justify-center gap-5 max-w-2xl mx-auto" style="animation-delay: 0.4s;">
    <a href="{{ config('services.android_app.play_store_url') }}"
       target="_blank"
       rel="noopener"
       aria-label="Get the Xplore Smithers app on Google Play"
       class="flex-shrink-0 group">
        <img src="{{ asset('images/explore-more-promo.jpeg') }}"
             alt=""
             class="w-24 md:w-32 h-auto rounded-xl shadow-2xl ring-1 ring-white/40 group-hover:scale-105 transition-transform duration-300"
             loading="lazy">
    </a>
    <div class="text-center md:text-left">
        <p class="text-emerald-200 text-xs md:text-sm font-semibold uppercase tracking-[0.18em] mb-2">
            Now available
        </p>
        <p class="text-white text-base md:text-lg font-medium mb-3 max-w-sm md:max-w-md">
            Take Xplore Smithers with you. Trails, lakes &amp; more — in your pocket.
        </p>
        <a href="{{ config('services.android_app.play_store_url') }}"
           target="_blank"
           rel="noopener"
           class="inline-flex items-center gap-3 bg-black hover:bg-gray-900 border border-white/30 hover:border-white/50 rounded-xl px-4 py-2.5 transition-all duration-300 shadow-lg hover:shadow-emerald-500/20">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92z" fill="#34A853"/>
                <path d="M16.81 15.013L6.05 21.21l8.13-8.131 2.63 1.934z" fill="#EA4335"/>
                <path d="M20.16 10.81a1 1 0 0 1 0 1.74l-3.35 1.93-2.63-2.48 2.63-2.48 3.35 1.29z" fill="#FBBC04"/>
                <path d="M14.18 12l-8.13-8.13L16.81 8.97l-2.63 3.03z" fill="#4285F4"/>
            </svg>
            <div class="flex flex-col leading-tight items-start">
                <span class="text-[10px] text-gray-300 uppercase tracking-wider">Get it on</span>
                <span class="text-white text-base font-semibold">Google Play</span>
            </div>
        </a>
    </div>
</div>
@endif
