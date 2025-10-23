<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Trail Finder - Discover Ethical Adventures')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Discover amazing hiking trails while promoting respectful, sustainable tourism. Explore with purpose, travel with respect.">
    <meta name="keywords" content="hiking trails, sustainable tourism, ethical adventures, trail finder, outdoor exploration">

    <!-- Fonts - Using Inter like XploreSmithers -->
    <link href="https://fonts.bunny.net/css?family=Inter:300,400,500,600,700,800" rel="stylesheet">
    
    <!-- Additional design fonts for headings -->
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:400,500,600,700" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation - Enhanced with XploreSmithers styling -->
    <nav class="fixed top-0 w-full bg-white/95 backdrop-blur-md shadow-lg z-50 transition-all duration-300">
        <div class="{{ request()->routeIs('map') ? 'w-full' : 'max-w-7xl mx-auto' }} px-4">
            <div class="flex justify-between items-center h-20">
                <!-- Logo - Enhanced with brand elements -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2 {{ request()->routeIs('map') ? 'hidden md:flex' : 'flex' }}">
                    <div class="relative">
                        <img src="{{ asset('images/logo.png') }}" 
                            alt="Trail Finder Logo" 
                            class="w-12 h-12 object-contain transition-all duration-300 group-hover:scale-105">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl font-bold text-gray-900 tracking-tight">Trail Finder</span>
                        <span class="text-xs text-accent-600 font-medium tracking-wider uppercase">Ethical Adventures</span>
                    </div>
                </a>

                 @if(request()->routeIs('map'))
                <div class="flex-1 {{ request()->routeIs('map') ? 'max-w-full md:max-w-5xl md:mx-8' : 'max-w-2xl mx-8' }}">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="global-trail-search"
                            placeholder="Search trails..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            autocomplete="off"
                        >
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <div id="search-suggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-xl border border-gray-200 max-h-96 overflow-y-auto z-50">
                            <!-- Suggestions will be inserted here -->
                        </div>
                    </div>
                </div>
                @endif
                

                <!-- Desktop Navigation - Enhanced with better spacing and hover effects -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="{{ route('home') }}" 
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('home') ? 'text-accent-700' : '' }}">
                        <span>Home</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('home') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('trails.index') }}" 
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('trails.*') ? 'text-accent-700' : '' }}">
                        <span>Browse Trails</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('trails.*') ? 'w-full' : '' }}"></div>
                    </a>
                    <a href="{{ route('map') }}" 
                       class="relative text-forest-700 hover:text-accent-600 font-medium transition-all duration-300 py-2 group {{ request()->routeIs('map') ? 'text-accent-700' : '' }}">
                        <span>Interactive Map</span>
                        <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-accent-600 group-hover:w-full transition-all duration-300 {{ request()->routeIs('map') ? 'w-full' : '' }}"></div>
                    </a>

                    <!-- New navigation items inspired by XploreSmithers -->
                    {{-- <div class="relative group">
                        <button class="text-gray-700 hover:text-emerald-600 font-medium transition-colors duration-300 flex items-center space-x-1">
                            <span>Community</span>
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div class="absolute top-full left-0 mt-2 w-48 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <div class="py-2">
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">Local Culture</a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">Conservation</a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">Trail Safety</a>
                                <a href="#" class="block px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">Leave No Trace</a>
                            </div>
                        </div>
                    </div> --}}
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
                        <a href="{{ route('trails.index') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Browse Trails</a>
                        <a href="{{ route('map') }}" class="block px-6 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 font-medium transition-colors">Interactive Map</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20">
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
            <div class="grid md:grid-cols-12 gap-8">
                <!-- Brand Section - Enhanced -->
                <div class="md:col-span-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="{{ asset('images/logo.png') }}" 
                        alt="Trail Finder Logo" 
                        class="w-12 h-12 object-contain">
                        <div>
                            <span class="text-2xl font-bold">Trail Finder</span>
                            <p class="text-accent-400 text-sm font-medium">Ethical Adventures</p>
                        </div>
                    </div>
                    
                    <!-- Mission statement inspired by XploreSmithers -->
                    <p class="text-gray-300 leading-relaxed mb-6">
                        We inspire connection through trail discovery. Experience nature in a way that's real, mindful, 
                        and unforgettable while promoting respectful, sustainable tourism.
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
                    <ul class="space-y-3">
                        <li><a href="{{ route('trails.index') }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🥾</span><span>All Trails</span>
                        </a></li>
                        <li><a href="{{ route('map') }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🗺️</span><span>Interactive Map</span>
                        </a></li>
                        <li><a href="{{ route('trails.index', ['difficulty' => 1]) }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>🌱</span><span>Easy Trails</span>
                        </a></li>
                        <li><a href="{{ route('trails.index', ['difficulty' => 3]) }}" class="text-gray-300 hover:text-accent-400 transition-colors duration-300 flex items-center space-x-2">
                            <span>⛰️</span><span>Challenging Trails</span>
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
                    <div class="space-y-6">
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
                        
                        <!-- Social links -->
                        <div>
                            <p class="text-gray-300 text-sm mb-4 font-medium">Follow our adventures</p>
                            <div class="flex space-x-3">
                                <a href="https://youtube.com/@xploresmithers?si=Q9jtjqElsvfcigNH" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                                <a href="https://www.instagram.com/xploresmithers?igsh=Y3huYTRtM243cTdi" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a href="https://www.tiktok.com/@xploresmithers?_t=ZS-90lIATag1Ld&_r=1" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.589 6.686a4.793 4.793 0 0 1-3.77-4.245V2h-3.445v13.672a2.896 2.896 0 0 1-5.201 1.743l-.002-.001.002.001a2.895 2.895 0 0 1 3.183-4.51v-3.5a6.329 6.329 0 0 0-5.394 10.692 6.33 6.33 0 0 0 10.857-4.424V8.687a8.182 8.182 0 0 0 4.773 1.526V6.79a4.831 4.831 0 0 1-1.003-.104z"/></svg>
                                </a>
                                <a href="https://www.facebook.com/share/1C9Q3PAT7i/" target="_blank" class="w-10 h-10 bg-green-700/60 hover:bg-accent-500 rounded-lg flex items-center justify-center transition-colors duration-300 group">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom footer with enhanced styling -->
            <div class="mt-12 pt-8 border-t border-green-800/60">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-6">
                        <p class="text-gray-400 text-sm">
                            &copy; {{ date('Y') }} Trail Finder. 
                            <span class="text-emerald-400 font-medium">Discover responsibly.</span>
                        </p>
                        <div class="hidden md:flex items-center space-x-4 text-xs text-gray-500">
                            <a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a>
                            <span>•</span>
                            <a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
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

    @stack('scripts')
    
    <!-- Add Alpine.js for interactive components -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>