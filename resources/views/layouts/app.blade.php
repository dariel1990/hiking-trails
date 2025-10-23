<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>{{ config('app.name', 'Hiking Trails') }} @yield('title')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="app">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ url('/') }}" class="text-xl font-bold text-primary-600">
                                🥾 {{ config('app.name', 'Hiking Trails') }}
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('trails.index') }}" class="nav-link {{ request()->routeIs('trails.*') ? 'active' : '' }}">
                                Trails
                            </a>
                            <a href="{{ url('/map') }}" class="nav-link {{ request()->is('map') ? 'active' : '' }}">
                                Map
                            </a>
                        </div>
                    </div>

                    <!-- Right Side Of Navbar -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        @guest
                            @if (Route::has('login'))
                                <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2">Login</a>
                            @endif

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary ml-4">Sign up</a>
                            @endif
                        @else
                            <!-- User Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                    <div>{{ Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>

                                <div x-show="open" @click="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50">
                                    <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                    <a href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>

                    <!-- Mobile menu button -->
                    <div class="sm:hidden flex items-center">
                        <button type="button" class="text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600" x-data="{ open: false }" @click="open = !open">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="min-h-screen">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mx-4 mt-4" role="alert">
                    {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white">
            <div class="max-w-7xl mx-auto py-12 px-4">
                <div class="grid md:grid-cols-4 gap-8">
                    <div class="col-span-2">
                        <h3 class="text-lg font-semibold mb-4">🥾 {{ config('app.name') }}</h3>
                        <p class="text-gray-300 max-w-md">
                            Discover amazing hiking trails, track your progress, and connect with the hiking community.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Explore</h4>
                        <ul class="space-y-2 text-gray-300">
                            <li><a href="{{ route('trails.index') }}" class="hover:text-white">All Trails</a></li>
                            <li><a href="/map" class="hover:text-white">Trail Map</a></li>
                            <li><a href="/difficulty/easy" class="hover:text-white">Easy Trails</a></li>
                            <li><a href="/difficulty/moderate" class="hover:text-white">Moderate Trails</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Account</h4>
                        <ul class="space-y-2 text-gray-300">
                            @auth
                                <li><a href="{{ route('profile') }}" class="hover:text-white">My Profile</a></li>
                                <li><a href="{{ route('profile') }}#completions" class="hover:text-white">My Completions</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-white">Login</a></li>
                                <li><a href="{{ route('register') }}" class="hover:text-white">Sign Up</a></li>
                            @endauth
                        </ul>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-700 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Built with Laravel & Tailwind CSS.</p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

<style>
.nav-link {
    @apply inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out;
}

.nav-link.active {
    @apply border-primary-500 text-primary-600;
}
</style>