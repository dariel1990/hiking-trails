@php
    // Never promote the app to someone already reading this inside the app.
    $isInNativeApp = visitor_in_native_app();
    // Phones only see the store they can actually install from; desktop sees both.
    $platform = visitor_mobile_platform();
    $showAppStore = config('services.ios_app.app_store_url') && $platform !== 'android';
    $showPlayStore = config('services.android_app.play_store_url') && $platform !== 'ios';
    $appStoreUrl = config('services.ios_app.app_store_url');
    $playStoreUrl = config('services.android_app.play_store_url');

    // Falls back to the older Play-era artwork until the dual-store poster is uploaded.
    $promoImage = file_exists(public_path('images/xplore-smithers-app-promo.jpg'))
        ? 'images/xplore-smithers-app-promo.jpg'
        : 'images/explore-more-promo.jpeg';
@endphp
@if(($showAppStore || $showPlayStore) && ! $isInNativeApp)
<!-- Mobile App Promo (hero) -->
<div class="slide-in-up mb-12 flex flex-col md:flex-row items-center justify-center gap-5 max-w-2xl mx-auto" style="animation-delay: 0.4s;">
    <img src="{{ asset($promoImage) }}"
         alt=""
         class="flex-shrink-0 w-24 md:w-32 h-auto rounded-xl shadow-2xl ring-1 ring-white/40"
         loading="lazy">
    <div class="text-center md:text-left">
        <p class="text-emerald-200 text-xs md:text-sm font-semibold uppercase tracking-[0.18em] mb-2">
            Now available
        </p>
        <p class="text-white text-base md:text-lg font-medium mb-3 max-w-sm md:max-w-md">
            Take Xplore Smithers with you. Trails, lakes &amp; more — in your pocket.
        </p>
        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
            @if($showAppStore)
            <a href="{{ $appStoreUrl }}"
               target="_blank"
               rel="noopener"
               aria-label="Download the Xplore Smithers app on the App Store"
               class="inline-flex items-center gap-3 bg-black hover:bg-gray-900 border border-white/30 hover:border-white/50 rounded-xl px-4 py-2.5 transition-all duration-300 shadow-lg hover:shadow-emerald-500/20">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M17.564 12.75c-.024-2.51 2.05-3.717 2.143-3.775-1.166-1.706-2.98-1.94-3.624-1.966-1.544-.156-3.013.91-3.797.91-.782 0-1.99-.887-3.272-.863-1.684.025-3.235.978-4.1 2.484-1.747 3.03-.447 7.52 1.254 9.98.83 1.204 1.82 2.558 3.122 2.51 1.253-.05 1.726-.81 3.24-.81 1.514 0 1.94.81 3.266.785 1.348-.024 2.203-1.228 3.03-2.437.955-1.398 1.348-2.75 1.372-2.82-.03-.013-2.633-1.01-2.66-4.01zM15.09 5.39c.69-.836 1.157-2 1.03-3.157-.995.04-2.2.662-2.914 1.497-.64.74-1.2 1.923-1.05 3.057 1.11.086 2.243-.564 2.934-1.397z"/>
                </svg>
                <div class="flex flex-col leading-tight items-start">
                    <span class="text-[10px] text-gray-300 uppercase tracking-wider">Download on the</span>
                    <span class="text-white text-base font-semibold">App Store</span>
                </div>
            </a>
            @endif

            @if($showPlayStore)
            <a href="{{ $playStoreUrl }}"
               target="_blank"
               rel="noopener"
               aria-label="Get the Xplore Smithers app on Google Play"
               class="inline-flex items-center gap-3 bg-black hover:bg-gray-900 border border-white/30 hover:border-white/50 rounded-xl px-4 py-2.5 transition-all duration-300 shadow-lg hover:shadow-emerald-500/20">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
            @endif
        </div>
    </div>
</div>
@endif
