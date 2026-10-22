<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} - E-Detimil</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Instrument Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes abstractAuraPulse {
            0%, 100% {
                opacity: 0.55;
                transform: scale(0.95);
            }
            50% {
                opacity: 0.95;
                transform: scale(1.15);
            }
        }
        @keyframes abstractBlobFloat1 {
            0%, 100% {
                transform: translate(0px, 0px) scale(1) rotate(0deg);
            }
            33% {
                transform: translate(12px, -8px) scale(1.1) rotate(15deg);
            }
            66% {
                transform: translate(-10px, 8px) scale(0.94) rotate(-10deg);
            }
        }
        @keyframes logoShimmerSweep {
            0% {
                transform: translateX(-150%) skewX(-20deg);
            }
            25%, 100% {
                transform: translateX(250%) skewX(-20deg);
            }
        }
        .animate-aura-pulse {
            animation: abstractAuraPulse 4s ease-in-out infinite;
        }
        .animate-blob-1 {
            animation: abstractBlobFloat1 8s ease-in-out infinite;
        }
        .animate-shimmer {
            animation: logoShimmerSweep 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-gradient-to-br from-slate-50 via-teal-50/30 to-emerald-50/40">
    {{ $slot ?? '' }}
    @yield('content')

    <!-- Universal Interaction & Navigation Page Loader -->
    <x-page-loader />
</body>
</html>

