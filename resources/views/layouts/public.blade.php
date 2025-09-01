<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Hiking Trails - Discover Amazing Adventures')</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white bg-opacity-95 backdrop-blur-sm shadow-sm z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Trail Finder</span>
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary-600 transition-colors {{ request()->routeIs('home') ? 'text-primary-600 font-semibold' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('trails.index') }}" class="text-gray-700 hover:text-primary-600 transition-colors {{ request()->routeIs('trails.*') ? 'text-primary-600 font-semibold' : '' }}">
                        Browse Trails
                    </a>
                    <a href="{{ route('map') }}" class="text-gray-700 hover:text-primary-600 transition-colors {{ request()->routeIs('map') ? 'text-primary-600 font-semibold' : '' }}">
                        Map View
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 hover:text-gray-900" x-data="{ open: false }" @click="open = !open">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span class="text-xl font-bold">Trail Finder</span>
                    </div>
                    <p class="text-gray-400 max-w-md">
                        Discover amazing hiking trails with detailed maps, photos, and information. 
                        Your next adventure awaits.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Explore</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('trails.index') }}" class="hover:text-white transition-colors">All Trails</a></li>
                        <li><a href="{{ route('map') }}" class="hover:text-white transition-colors">Map View</a></li>
                        <li><a href="{{ route('trails.index', ['difficulty' => 1]) }}" class="hover:text-white transition-colors">Easy Trails</a></li>
                        <li><a href="{{ route('trails.index', ['difficulty' => 3]) }}" class="hover:text-white transition-colors">Challenging Trails</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Information</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Trail Safety</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Leave No Trace</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Weather Updates</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Trail Finder. Discover responsibly.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>