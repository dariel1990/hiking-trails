<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Not Found — {{ setting('site_name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(setting('footer_logo_path')) }}">

    <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700,800" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=Playfair+Display:600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-hero-gradient flex items-center justify-center px-4 py-16 relative overflow-hidden">
        <!-- Subtle mountain pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="mountain-pattern-404" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <path d="M10 2L18 16H2L10 2Z" fill="currentColor" class="text-white" opacity="0.4"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#mountain-pattern-404)"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-xl w-full text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 mb-10">
                <img src="{{ asset(setting('header_logo_path')) }}"
                     alt="{{ setting('site_name') }} Logo"
                     class="w-14 h-14 object-contain drop-shadow">
                <span class="text-2xl font-bold text-white tracking-tight">{{ setting('site_name') }}</span>
            </a>

            <p class="font-display text-[7rem] leading-none font-bold text-white/95 drop-shadow-lg">404</p>

            <h1 class="text-2xl md:text-3xl font-bold text-white mb-3 mt-2">
                Looks like this trail doesn't exist
            </h1>
            <p class="text-white/80 text-base md:text-lg mb-10 max-w-md mx-auto">
                The page you're looking for may have been moved, renamed, or is no longer available.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <button type="button" onclick="xsGoBack()"
                    class="inline-flex items-center gap-2 bg-white text-forest-700 hover:bg-sand-100 font-semibold py-3 px-7 rounded-xl transition-all duration-300 shadow-xl hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Go Back
                </button>
                <a href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 bg-forest-800/60 hover:bg-forest-800 text-white font-semibold py-3 px-7 rounded-xl border border-white/25 transition-all duration-300">
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function xsGoBack() {
            const cameFromSameSite = document.referrer && document.referrer.indexOf(window.location.host) !== -1;
            if (cameFromSameSite && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '{{ url('/') }}';
            }
        }
    </script>
</body>
</html>
