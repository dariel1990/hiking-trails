<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- XploreSmithers Pro: web entitlement flag consumed by the gating helpers.
         When the subscriptions switch is off, every web visitor is treated as
         entitled so all Pro features are unlocked and the paywall never shows. --}}
    <script>
        window.xsWeb = {
            subscriptionsEnabled: {{ subscriptions_enabled() ? 'true' : 'false' }},
            entitled: {{ ! subscriptions_enabled() || (auth()->check() && auth()->user()->hasActiveProEntitlement()) ? 'true' : 'false' }},
            loggedIn: {{ auth()->check() ? 'true' : 'false' }},
            proUrl: @json(route('pro.show'))
        };
        window.xsAppDownloadUrl = @json(config('services.android_app.play_store_url'));
    </script>
    <style>[x-cloak]{display:none !important;}</style>

    <title>@yield('title', setting('default_page_title'))</title>
    <!-- Meta Tags Stack -->
    @stack('meta')
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-E170L5ZVE8"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-E170L5ZVE8');
    </script>
    <link rel="icon" type="image/png" href="{{ asset(setting('footer_logo_path')) }}">

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ setting('meta_description') }}">
    <meta name="keywords" content="{{ setting('meta_keywords') }}">

    <!-- Fonts - Using Inter like XploreSmithers -->
    <link href="https://fonts.bunny.net/css?family=Inter:300,400,500,600,700,800" rel="stylesheet">
    
    <!-- Additional design fonts for headings -->
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:400,500,600,700" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    @unless(request()->routeIs('login', 'register'))
    <!-- Navigation - Enhanced with XploreSmithers styling -->
    <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-md shadow-lg z-50 transition-all duration-300 {{ request()->routeIs('map') ? 'max-md:hidden' : '' }}">
        <div class="w-full px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo - Enhanced with brand elements -->
                <a href="{{ setting('main_site_url') ?: url('/') }}" class="flex items-center space-x-2 {{ request()->routeIs('map') ? 'hidden md:flex' : 'flex' }}">
                    <div class="relative">
                        <img src="{{ asset(setting('header_logo_path')) }}"
                            alt="{{ setting('site_name') }} Logo"
                            class="w-12 h-12 object-contain transition-all duration-300 group-hover:scale-105">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900 tracking-tight">{{ setting('site_name') }}</span>
                        <span class="text-xs text-accent-600 font-medium tracking-wider uppercase">{{ setting('tagline') }}</span>
                    </div>
                </a>

                

                <!-- Desktop Navigation - Enhanced with better spacing and hover effects -->
                <div class="hidden lg:flex items-center space-x-8">                    
                    <a href="{{ route('home') }}" 
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('home') ? 'text-accent-700' : '' }}">
                        <span>Home</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('home') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('trails.index') }}"
                    class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('trails.index') ? 'text-accent-700' : '' }}">
                        <span>Hiking Trails</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('trails.index') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('fishing-lakes.index') }}"
                    class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('fishing-lakes.index') ? 'text-accent-700' : '' }}">
                        <span>Fishing Lakes</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('fishing-lakes.index') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('trail-networks.index') }}"
                    class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('trail-networks.*') ? 'text-accent-700' : '' }}">
                        <span>Ski Trails</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('trail-networks.*') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('tours.index') }}"
                    class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('tours.*') ? 'text-accent-700' : '' }}">
                        <span>Tours</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('tours.*') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('map') }}"
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('map') ? 'text-accent-700' : '' }}">
                        <span>Interactive Map</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('map') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('businesses.public.index') }}"
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('businesses.public.*') ? 'text-accent-700' : '' }}">
                        <span>Local Businesses</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('businesses.public.*') ? 'w-full' : '' }}"></div>
                    </a>

                    @guest
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-white font-medium px-5 py-2 rounded-lg shadow-sm transition-all duration-300">
                            <span>Sign in</span>
                        </a>
                    @endguest

                    @auth
                        @php($navUser = auth()->user())
                        @php($navIsPro = $navUser->hasActiveProEntitlement())
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" @click="open = !open" @keydown.escape.window="open = false"
                                    :aria-expanded="open" aria-haspopup="true"
                                    class="flex items-center gap-2.5 rounded-full pl-1 pr-2.5 py-1 hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-forest-600/20">
                                <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-forest-600 to-forest-800 text-white text-sm font-semibold shadow-sm overflow-hidden">
                                    @if($navUser->avatar_url)
                                        <img src="{{ $navUser->avatar_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ $navUser->initials }}
                                    @endif
                                    @if($navIsPro)
                                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full bg-accent-500 ring-2 ring-white" title="XploreSmithers Pro"></span>
                                    @endif
                                </span>
                                <span class="hidden xl:block text-sm font-medium text-forest-700 max-w-[120px] truncate">{{ $navUser->name }}</span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                                 class="absolute right-0 mt-2 w-64 origin-top-right rounded-2xl bg-white shadow-xl ring-1 ring-black/5 overflow-hidden z-50">
                                <div class="flex items-center gap-3 px-4 py-4 bg-gray-50 border-b border-gray-100">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-forest-600 to-forest-800 text-white text-sm font-semibold overflow-hidden">
                                        @if($navUser->avatar_url)
                                            <img src="{{ $navUser->avatar_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            {{ $navUser->initials }}
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $navUser->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $navUser->email }}</p>
                                    </div>
                                </div>

                                <div class="py-1.5">
                                    @if(subscriptions_enabled())
                                        @if($navIsPro)
                                            <p class="flex items-center gap-2 px-4 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wide text-accent-600">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                                XploreSmithers Pro
                                            </p>
                                            <a href="{{ route('pro.portal') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.27 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.27-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Manage subscription
                                            </a>
                                        @else
                                            <button type="button" onclick="window.xsShowProModal()" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-accent-600 hover:bg-accent-50 transition-colors">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.367-2.447a1 1 0 00-1.176 0l-3.367 2.447c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.07 9.384c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.957z"/></svg>
                                                Go Pro
                                            </button>
                                        @endif
                                        <div class="my-1.5 border-t border-gray-100"></div>
                                    @endif

                                    <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.27 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.27-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Settings
                                    </a>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                            Log out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>

                <!-- CTA Button - Enhanced design -->
                
                <!-- Mobile menu button - Enhanced -->
                <div class="lg:hidden" x-data="{ mobileOpen: false }">
                    <button type="button" 
                            @click="mobileOpen = !mobileOpen"
                            class="text-gray-700 hover:text-emerald-600 p-2 rounded-lg hover:bg-emerald-50 transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                <div x-show="mobileOpen" 
                    @click.away="mobileOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="absolute top-full left-0 right-0 bg-white shadow-xl border-t border-gray-100">
                    <div class="py-4 space-y-2">
                        <a href="{{ route('home') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Home</a>
                        <a href="{{ route('trails.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Hiking Trails</a>
                        <a href="{{ route('fishing-lakes.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Fishing Lakes</a>
                        <a href="{{ route('trail-networks.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Ski Trails</a>
                        <a href="{{ route('tours.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Tours</a>
                        <a href="{{ route('map') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Interactive Map</a>
                        <a href="{{ route('businesses.public.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Local Businesses</a>

                        @guest
                            <a href="{{ route('login') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Sign in</a>
                            <a href="{{ route('register') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Create account</a>
                        @endguest

                        @auth
                            <div class="mt-2 px-6 py-3 border-t border-gray-100 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-forest-600 to-forest-800 text-white text-sm font-semibold overflow-hidden">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ auth()->user()->initials }}
                                    @endif
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            @if(subscriptions_enabled())
                                @if(auth()->user()->hasActiveProEntitlement())
                                    <a href="{{ route('pro.portal') }}" class="block px-6 py-3 font-medium text-accent-700 transition-colors">
                                        ★ Manage subscription
                                    </a>
                                @else
                                    <button type="button" onclick="window.xsShowProModal()" class="w-full text-left block px-6 py-3 font-medium text-accent-600 hover:bg-emerald-50 transition-colors">
                                        Go Pro
                                    </button>
                                @endif
                            @endif
                            <a href="{{ route('settings.profile') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Log out</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endunless

    <!-- Main Content -->
    <main class="{{ request()->routeIs('login', 'register') ? '' : 'pt-20' }} {{ request()->routeIs('map') ? 'max-md:pt-0' : '' }}">
        <!-- Flash Messages with enhanced styling -->
        @if(session('success'))
            <div class="fixed top-24 right-6 z-40 max-w-sm">
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                            <svg class="w-4 h-4 text-emerald-500 hover:text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="fixed top-24 right-6 z-40 max-w-sm">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto">
                            <svg class="w-4 h-4 text-red-500 hover:text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @unless(request()->routeIs('map') || request()->routeIs('trail-networks.show') || request()->routeIs('admin.login') || request()->routeIs('login', 'register'))
    <!-- Enhanced Footer inspired by XploreSmithers -->
    <footer class="bg-gray-900 text-white relative overflow-hidden" style="background: linear-gradient(135deg, #2C5F5D 0%, #1a2e2e 100%);">
        <!-- Subtle background pattern -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="mountain-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <path d="M10 2L18 16H2L10 2Z" fill="currentColor" opacity="0.1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#mountain-pattern)"/>
            </svg>
        </div>

        <div class="relative max-w-7xl mx-auto py-16 px-4 lg:px-8">
            <div class="grid md:grid-cols-12 gap-8 text-center md:text-left">
                <!-- Brand Section - Enhanced -->
                <div class="md:col-span-6">
                    <div class="flex flex-col md:flex-row items-center md:space-x-3 space-y-3 md:space-y-0 mb-6">
                        <img src="{{ asset(setting('footer_logo_path')) }}"
                        alt="{{ setting('footer_brand_name') }} Logo"
                        class="w-12 h-12 object-contain">
                        <div class="text-center md:text-left">
                            <span class="text-2xl font-bold block">{{ setting('footer_brand_name') }}</span>
                            <p class="text-accent-400 text-sm font-medium">{{ setting('footer_tagline') }}</p>
                        </div>
                    </div>

                    <!-- Mission statement inspired by XploreSmithers -->
                    <p class="text-gray-300 leading-relaxed mb-6">
                        {{ setting('footer_mission_text') }}
                    </p>
                    
                    <div class="bg-emerald-900/30 rounded-xl p-4 border border-emerald-800/40">
                        <p class="text-accent-400 font-semibold text-sm mb-2">Our Mission</p>
                        <p class="text-gray-300 text-sm">
                            <strong>Explore with purpose. Travel with respect.</strong><br>
                            Supporting local communities and natural conservation.
                        </p>
                    </div>
                </div>
                
                <!-- Explore Links -->
                <div class="md:col-span-3">
                    <h4 class="font-bold text-lg mb-6 text-accent-400">Explore</h4>
                    <ul class="space-y-3 flex flex-col items-center md:items-start">
                        <li><a href="{{ route('trails.index') }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🥾</span><span>Hiking Trails</span>
                        </a></li>
                        <li><a href="{{ route('fishing-lakes.index') }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🐟</span><span>Fishing Lakes</span>
                        </a></li>
                        <li><a href="{{ route('map') }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🗺️</span><span>Interactive Map</span>
                        </a></li>
                        <li><a href="{{ route('trails.index', ['difficulty' => 1]) }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🌱</span><span>Easy Trails</span>
                        </a></li>
                    </ul>
                </div>
                
                <!-- Community & Ethics -->
                {{-- <div class="md:col-span-3">
                    <h4 class="font-bold text-lg mb-6 text-emerald-400">Community</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-emerald-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🏛️</span><span>Local Culture</span>
                        </a></li>
                        <li><a href="#" class="text-gray-300 hover:text-emerald-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🌿</span><span>Conservation</span>
                        </a></li>
                        <li><a href="#" class="text-gray-300 hover:text-emerald-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>⚠️</span><span>Trail Safety</span>
                        </a></li>
                        <li><a href="#" class="text-gray-300 hover:text-emerald-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>♻️</span><span>Leave No Trace</span>
                        </a></li>
                    </ul>
                </div> --}}
                
                <!-- Connect With Us - Fixed -->
                <div class="md:col-span-3">
                    <h4 class="font-bold text-lg mb-6 text-accent-400">Connect With Us</h4>
                    <div class="space-y-6 flex flex-col items-center md:items-start">
                        <!-- Newsletter signup - Fixed styling -->
                        <div class="bg-forest-800/60 rounded-xl p-5 border border-forest-700/70">
                            <h5 class="font-semibold text-white text-base mb-4">Trail Updates</h5>
                            <form class="space-y-3">
                                <input type="email" placeholder="Your email" 
                                    class="w-full px-4 py-3 bg-forest-700/60 border border-forest-600/70 rounded-lg text-sm text-white placeholder-gray-300 focus:outline-none focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400 transition-colors">
                                <button type="submit" class="w-full bg-forest-600 hover:bg-accent-500 text-white px-4 py-3 rounded-lg text-sm font-semibold transition-colors duration-300">
                                    Subscribe
                                </button>
                            </form>
                        </div>
                        
                        @if(config('services.android_app.play_store_url'))
                        <!-- Get the app -->
                        <div class="mb-6 hidden lg:block">
                            <p class="text-gray-300 text-sm mb-3 font-medium">Get the app</p>
                            <a href="{{ config('services.android_app.play_store_url') }}"
                               target="_blank"
                               rel="noopener"
                               class="inline-flex items-center gap-3 bg-black hover:bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 transition-colors duration-300">
                                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92z" fill="#34A853"/>
                                    <path d="M16.81 15.013L6.05 21.21l8.13-8.131 2.63 1.934z" fill="#EA4335"/>
                                    <path d="M20.16 10.81a1 1 0 0 1 0 1.74l-3.35 1.93-2.63-2.48 2.63-2.48 3.35 1.29z" fill="#FBBC04"/>
                                    <path d="M14.18 12l-8.13-8.13L16.81 8.97l-2.63 3.03z" fill="#4285F4"/>
                                </svg>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[10px] text-gray-300 uppercase tracking-wider">Get it on</span>
                                    <span class="text-white text-base font-semibold">Google Play</span>
                                </div>
                            </a>
                        </div>
                        @endif

                        <!-- Social links -->
                        <div>
                            <p class="text-gray-300 text-sm mb-4 font-medium">Follow our adventures</p>
                            <div class="flex space-x-3 justify-center md:justify-start">
                                @if(setting('social_youtube_url'))
                                <a href="{{ setting('social_youtube_url') }}" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                                @endif
                                @if(setting('social_instagram_url'))
                                <a href="{{ setting('social_instagram_url') }}" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                @endif
                                @if(setting('social_tiktok_url'))
                                <a href="{{ setting('social_tiktok_url') }}" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-5.394 10.692 6.33 6.33 0 0 0 10.857-4.424V8.687a8.182 8.182 0 0 0 4.773 1.526V6.79a4.831 4.831 0 0 1-1.003-.104z"/></svg>
                                </a>
                                @endif
                                @if(setting('social_facebook_url'))
                                <a href="{{ setting('social_facebook_url') }}" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom footer with enhanced styling -->
            <div class="mt-12 pt-8 border-t border-green-800/60">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 text-center md:text-left">
                    <div class="flex items-center space-x-6">
                        <p class="text-gray-400 text-sm">
                            &copy; {{ date('Y') }} {{ setting('copyright_text') }}
                            <span class="text-emerald-400 font-medium">Discover responsibly.</span>
                        </p>
                        <div class="hidden md:flex items-center space-x-4 text-xs text-gray-500">
                            <a href="{{ route('privacy-policy') }}" class="hover:text-emerald-400 transition-colors">Privacy Policy</a>
                            <span>•</span>
                            <a href="{{ route('terms') }}" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
                            <span>•</span>
                            <a href="#" class="hover:text-emerald-400 transition-colors">Accessibility</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span>Made with care for nature</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    {{-- XploreSmithers Pro upgrade modal (web gating) --}}
    @include('subscription._upgrade-modal')


    @stack('scripts')
</body>
</html>