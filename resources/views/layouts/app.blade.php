<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - E-Detimil</title>
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
                            200: '#99f6e4',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Flatpickr (Format Hari-Bulan-Tahun & Kalender Interaktif) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_teal.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
        }
        /* Custom Styling Flatpickr Hari-Bulan-Tahun */
        .flatpickr-calendar {
            font-family: '"Instrument Sans"', sans-serif !important;
            border-radius: 1.25rem !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px -5px rgba(13, 148, 136, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            padding: 0.5rem !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #0d9488 !important;
            border-color: #0d9488 !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
        }
        .flatpickr-day:hover {
            background: #ccfbf1 !important;
            border-color: #99f6e4 !important;
            color: #0f766e !important;
            border-radius: 0.75rem !important;
        }
        .flatpickr-current-month {
            font-weight: 700 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex">
        <!-- Sidebar Mobile Backdrop -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden">
        </div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shadow-sm">
            
            <!-- Brand Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100 bg-gradient-to-r from-teal-600 to-emerald-600 text-white">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E-Detimil" class="w-10 h-10 rounded-xl shadow-inner object-cover" />
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white block">E-Detimil</span>
                        <span class="text-xs text-teal-100 block font-normal">Deteksi Dini Bumil Risiko</span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- User Brief Card in Sidebar -->
            <div class="p-3.5 mx-4 my-3 bg-slate-50/80 rounded-2xl border border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center text-sm shadow-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold
                            {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-700' : (auth()->user()->isBidan() ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ auth()->user()->role->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Menu Navigation Links -->
            <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Menu Utama</div>

                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('dashboard*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard Capaian</span>
                </a>

                <!-- Skrining SPR -->
                <a href="{{ route('skrining.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('skrining*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('skrining*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span>Skrining Skor SPR</span>
                </a>

                <!-- Data Ibu Hamil -->
                <a href="{{ route('ibu-hamil.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('ibu-hamil*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('ibu-hamil*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Data Ibu Hamil</span>
                </a>

                <!-- Kunjungan ANC (Semua Role) -->
                <a href="{{ route('kunjungan.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('kunjungan*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('kunjungan*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Kunjungan ANC</span>
                </a>

                @if(!auth()->user()->isKader())
                    <!-- Laporan (Admin & Bidan) -->
                    <a href="{{ route('laporan.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                       {{ request()->routeIs('laporan*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('laporan*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan & Excel</span>
                    </a>
                @endif

                <div class="pt-4 pb-1">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Master & Wilayah</div>
                </div>

                <!-- Wilayah Kerja Binaan -->
                <a href="{{ route('desa.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('desa*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('desa*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Wilayah Kerja Binaan</span>
                </a>

                @if(!auth()->user()->isKader())
                    <!-- Manajemen Pengguna (Admin & Bidan) -->
                    <a href="{{ route('users.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                       {{ request()->routeIs('users*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('users*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>{{ auth()->user()->isAdmin() ? 'Manajemen Pengguna' : 'Kelola Kader' }}</span>
                    </a>
                @endif

                <div class="pt-4 pb-1">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 mb-2">Akun Saya</div>
                </div>

                <!-- Pengaturan Akun -->
                <a href="{{ route('profile.show') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors
                   {{ request()->routeIs('profile*') ? 'bg-teal-50 text-teal-700 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('profile*') ? 'text-teal-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Pengaturan Akun</span>
                </a>

                <!-- Keluar -->
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header Bar -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-xs sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-slate-800">{{ $header ?? $title ?? 'E-Detimil' }}</h1>
                    </div>
                </div>

                <!-- Right Quick Info -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex flex-col text-right">
                        <span class="text-xs font-semibold text-slate-700">{{ now()->translatedFormat('l, d F Y') }}</span>
                        <span class="text-[11px] font-medium text-teal-700 truncate max-w-[260px]" title="{{ auth()->user()?->instansi ?? 'Puskesmas Digital' }}">
                            {{ auth()->user()?->instansi ?: (auth()->user()?->desa ? 'Desa ' . auth()->user()->desa->nama : (auth()->user()?->kecamatan_kerja ?: 'Puskesmas Digital')) }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Pop-Up Alerts (Modal Peringatan & Toast) -->
            <!-- Toast Pop-Up Success -->
            @if(session('success'))
                <div x-data="{ show: true }"
                     x-cloak
                     x-show="show"
                     x-init="setTimeout(() => show = false, 4500)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-[-16px] scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-[-16px] scale-95"
                     class="fixed top-5 right-5 z-50 max-w-sm sm:max-w-md w-full pointer-events-auto">
                    <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-xl shadow-emerald-950/10 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <h4 class="text-xs font-bold text-slate-800">Berhasil Disimpan</h4>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed font-medium">{{ session('success') }}</p>
                        </div>
                        <button type="button" @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Modal Pop-Up Peringatan / Kesalahan Validasi -->
            @if(session('error') || $errors->any())
                <div x-data="{ open: true }"
                     x-cloak
                     x-show="open"
                     @keydown.escape.window="open = false"
                     class="fixed inset-0 z-50 overflow-y-auto"
                     role="dialog" aria-modal="true">
                    <!-- Backdrop Blur Overlay -->
                    <div x-show="open"
                         x-transition:enter="transition-opacity ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition-opacity ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="open = false"
                         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity">
                    </div>

                    <!-- Modal Dialog Centered -->
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-90 translate-y-4 sm:translate-y-0"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-90 translate-y-4 sm:translate-y-0"
                             @click.stop
                             class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-100">
                            
                            <!-- Decorative Top Accent Bar -->
                            <div class="h-2 bg-gradient-to-r from-rose-500 via-rose-600 to-amber-500"></div>

                            <div class="p-6 sm:p-7">
                                <div class="flex items-start gap-4">
                                    <!-- Icon Warning with pulse ring -->
                                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 shadow-inner">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                                Pemberitahuan Sistem
                                            </h3>
                                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors" title="Tutup">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Terdapat data yang perlu diperiksa dan disesuaikan kembali:
                                        </p>
                                    </div>
                                </div>

                                <!-- Error Items Container -->
                                <div class="mt-4 pt-3 border-t border-slate-100 space-y-2.5 max-h-64 overflow-y-auto pr-1">
                                    @if(session('error'))
                                        <div class="flex items-start gap-2.5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold leading-relaxed">
                                            <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ session('error') }}</span>
                                        </div>
                                    @endif

                                    @if($errors->any())
                                        @foreach($errors->all() as $error)
                                            <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-rose-50/70 border border-rose-200/80 text-rose-800 text-xs font-medium leading-relaxed">
                                                <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="flex-1">{{ $error }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Footer Action Button -->
                                <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2.5">
                                    <button type="button"
                                            @click="open = false"
                                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800 text-white font-bold text-xs shadow-md shadow-rose-600/20 hover:shadow-lg transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Mengerti dan Perbaiki</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function initDatepickers(root = document) {
            if (typeof flatpickr !== 'undefined') {
                flatpickr.localize(flatpickr.l10ns.id);
                root.querySelectorAll('.datepicker-dmy').forEach(function(el) {
                    if (!el._flatpickr) {
                        flatpickr(el, {
                            altInput: true,
                            altFormat: "d-m-Y",
                            dateFormat: "Y-m-d",
                            allowInput: true,
                            locale: "id",
                            altInputClass: el.className,
                            monthSelectorType: "static"
                        });
                    }
                });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            initDatepickers();
        });
    </script>
    @stack('scripts')
</body>
</html>

