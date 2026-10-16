<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Detimil - Deteksi Dini Risiko Tinggi Ibu Hamil (Skor Poedji Rochjati)</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
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
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col justify-between selection:bg-teal-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="w-full bg-white/80 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 flex items-center justify-center text-white shadow-md shadow-teal-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 block">E-Detimil</span>
                    <span class="text-xs text-teal-700 block font-medium -mt-1">Puskesmas Digital Pelayanan Ibu Hamil</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold shadow-md transition-all">
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs sm:text-sm font-bold shadow-md transition-all">
                        <span>Masuk ke Sistem</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-50 border border-teal-200 text-teal-800 text-xs font-semibold mb-6 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                <span>Standar Baku Skor Poedji Rochjati (SPR)</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-extrabold text-slate-900 tracking-tight max-w-4xl mx-auto leading-tight sm:leading-none">
                Sistem Deteksi Dini & Pengendalian Risiko Tinggi <span class="bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">Ibu Hamil</span>
            </h1>

            <p class="mt-6 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                Platform terpadu untuk <strong>Admin Puskesmas</strong>, <strong>Bidan Desa</strong>, dan <strong>Kader Posyandu</strong> guna melakukan skrining 23 faktor risiko obstetri, pemantauan kunjungan ANC, dan pelaporan eksekutif wilayah.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('login') }}" 
                   class="px-8 py-3.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold shadow-lg shadow-teal-600/20 transition-all hover:shadow-xl hover:-translate-y-0.5">
                    Mulai Akses E-Detimil →
                </a>
            </div>

            <!-- 3 Roles Overview Cards -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <!-- Admin Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs hover:border-purple-300 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 font-bold flex items-center justify-center text-xl mb-4">
                        👑
                    </div>
                    <span class="text-xs font-semibold text-purple-700 uppercase tracking-wider block">Tingkat Manajerial</span>
                    <h3 class="text-lg font-bold text-slate-800 mt-1">Administrator</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Akses penuh ke semua desa. Memantau diagram capaian kunjungan hadir & dilayani, perbandingan antar desa, mengelola akun bides & kader, dan mengunduh seluruh laporan Excel wilayah.
                    </p>
                </div>

                <!-- Bidan Desa Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs hover:border-teal-300 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 font-bold flex items-center justify-center text-xl mb-4">
                        🩺
                    </div>
                    <span class="text-xs font-semibold text-teal-700 uppercase tracking-wider block">Tingkat Pelaksana Desa</span>
                    <h3 class="text-lg font-bold text-slate-800 mt-1">Bidan Desa (Bides)</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Akses penuh di desanya (kecuali menambah bides). Mengelola data ibu hamil, skrining SPR, mencatat kunjungan ANC hadir/dilayani, membina kader, dan mengunduh laporan khusus desa penugasan.
                    </p>
                </div>

                <!-- Kader Posyandu Card -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs hover:border-blue-300 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xl mb-4">
                        🤝
                    </div>
                    <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider block">Tingkat Komunitas Lapangan</span>
                    <h3 class="text-lg font-bold text-slate-800 mt-1">Kader Posyandu</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Menginput skrining ibu hamil dengan pertanyaan SPR, mengkategorikan risiko secara otomatis, melihat capaian desa (persentase) & kinerja pribadi di dashboard (tanpa hak unduh laporan).
                    </p>
                </div>
            </div>

            <!-- Risk Categories Legend -->
            <div class="mt-16 bg-white p-8 rounded-3xl border border-slate-200/80 shadow-xs text-left">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-slate-800">Tiga Kategori Tingkat Risiko Skor Poedji Rochjati (SPR)</h3>
                    <p class="text-xs text-slate-500">Klasifikasi otomatis berdasarkan total akumulasi skor faktor risiko obstetri</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-emerald-800 text-sm">KRR (Risiko Rendah)</span>
                            <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-emerald-200 text-emerald-900">Skor = 2</span>
                        </div>
                        <p class="text-xs text-emerald-700 mt-2 leading-relaxed">
                            Kehamilan fisiologis tanpa faktor bahaya. Persalinan dapat ditangani oleh Bidan di Polindes/Puskesmas.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-amber-800 text-sm">KRT (Risiko Tinggi)</span>
                            <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-amber-200 text-amber-900">Skor 6 - 10</span>
                        </div>
                        <p class="text-xs text-amber-700 mt-2 leading-relaxed">
                            Memiliki 1 atau lebih faktor risiko APGO / AGO. Penolong persalinan Bidan dan Dokter di Puskesmas PONED / RS.
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-rose-900 text-sm">KRST (Sangat Tinggi)</span>
                            <span class="font-extrabold text-xs px-2 py-0.5 rounded bg-rose-200 text-rose-900">Skor ≥ 12</span>
                        </div>
                        <p class="text-xs text-rose-800 mt-2 leading-relaxed font-medium">
                            Kondisi darurat obstetri (AGDO) atau komplikasi ganda. Rujukan segera ke Rumah Sakit oleh Dokter SpOG.
                        </p>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <!-- Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>&copy; {{ date('Y') }} <strong>E-Detimil</strong> • Sistem Deteksi Dini Risiko Tinggi Ibu Hamil</span>
            <span>Berbasis Skor Poedji Rochjati • Tailwind CSS 4 • Laravel 13</span>
        </div>
    </footer>

</body>
</html>
