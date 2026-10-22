@extends('layouts.guest', ['title' => 'Pendaftaran Akun Baru'])

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8" x-data="{
    lat: '{{ old('latitude') }}',
    lng: '{{ old('longitude') }}',
    gettingLocation: false,
    searchQuery: '',
    searchLoading: false,
    detectedGoogle: null,

    async getLocationGoogle() {
        if (!navigator.geolocation) {
            alert('Perangkat Anda tidak mendukung geolokasi GPS.');
            return;
        }
        this.gettingLocation = true;
        navigator.geolocation.getCurrentPosition(
            async (pos) => {
                this.lat = pos.coords.latitude.toFixed(7);
                this.lng = pos.coords.longitude.toFixed(7);
                await this.fetchGoogleData(pos.coords.latitude, pos.coords.longitude);
                this.gettingLocation = false;
            },
            (err) => {
                alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                this.gettingLocation = false;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    },

    async searchGoogleLocation() {
        if (!this.searchQuery.trim()) return;
        this.searchLoading = true;
        try {
            const res = await fetch('/api/locations/geocode?address=' + encodeURIComponent(this.searchQuery));
            const data = await res.json();
            if (data.success && data.latitude && data.longitude) {
                this.lat = Number(data.latitude).toFixed(7);
                this.lng = Number(data.longitude).toFixed(7);
                this.applyGoogleData(data);
            } else {
                alert(data.message || 'Wilayah tidak ditemukan di Google.');
            }
        } catch (e) {
            alert('Gagal menghubungi Google Geocoding.');
        } finally {
            this.searchLoading = false;
        }
    },

    async fetchGoogleData(lat, lng) {
        try {
            const res = await fetch('/api/locations/reverse-geocode?latitude=' + lat + '&longitude=' + lng);
            const data = await res.json();
            if (data.success) {
                this.applyGoogleData(data);
            }
        } catch (e) {
            console.error('Reverse geocode error', e);
        }
    },

    applyGoogleData(data) {
        this.detectedGoogle = data;
        const desaSelect = document.getElementById('desa_id');
        if (desaSelect && data.matched_desa_id) {
            desaSelect.value = data.matched_desa_id;
        }
    }
}">
    <div class="sm:mx-auto sm:w-full sm:max-w-xl">
        <!-- Brand Logo & Header with Abstract Green Animated Background -->
        <div class="flex justify-center">
            <div class="relative group">
                <!-- Abstract Green Animated Aura & Floating Blobs -->
                <div class="absolute -inset-3 rounded-3xl bg-gradient-to-tr from-emerald-500 via-teal-400 to-green-400 opacity-70 blur-xl animate-aura-pulse"></div>
                <div class="absolute -top-3 -right-3 w-12 h-12 rounded-full bg-emerald-300/60 blur-md animate-blob-1"></div>
                
                <!-- Glassmorphic Logo Container -->
                <div class="relative w-18 h-18 sm:w-20 sm:h-20 p-1.5 rounded-2xl bg-white/70 backdrop-blur-md border border-white/60 shadow-xl shadow-teal-700/20 flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:scale-105">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo E-Detimil" class="w-full h-full rounded-xl object-cover shadow-inner" />
                    <!-- Shimmer sweep -->
                    <div class="absolute inset-0 w-1/2 h-full bg-gradient-to-r from-transparent via-white/50 to-transparent animate-shimmer pointer-events-none"></div>
                </div>
            </div>
        </div>
        <h2 class="mt-4 text-center text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Pendaftaran Akun Baru
        </h2>
        <p class="mt-1.5 text-center text-xs sm:text-sm text-slate-600">
            Daftar sebagai <strong class="text-teal-700">Bidan Desa</strong> atau <strong class="text-teal-700">Kader Posyandu</strong><br>
            Akun akan diverifikasi langsung oleh Administrator Puskesmas / Dinas Kesehatan
        </p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-xl px-4">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-100 sm:px-10">
            
            @if(session('error') || $errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    {{ session('error') ?? $errors->first() }}
                </div>
            @endif

            <form class="space-y-4" action="{{ route('register') }}" method="POST">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Nama Lengkap <span class="text-rose-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}"
                           placeholder="Nama lengkap beserta gelar (jika ada)"
                           class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            No. Handphone / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}"
                               placeholder="08xxxxxxxxxx"
                               class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Nomor aktif untuk koordinasi dan pendampingan</span>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                               placeholder="nama@contoh.id"
                               class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Mendaftar Sebagai <span class="text-rose-500">*</span>
                        </label>
                        <select id="role" name="role" required
                                class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="kader" {{ old('role') === 'kader' ? 'selected' : '' }}>Kader Posyandu</option>
                            <option value="bidan" {{ old('role') === 'bidan' ? 'selected' : '' }}>Bidan Desa (Bides)</option>
                        </select>
                    </div>

                    <div>
                        <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Wilayah Desa Penugasan <span class="text-rose-500">*</span>
                        </label>
                        <select id="desa_id" name="desa_id" required
                                class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Wilayah Desa --</option>
                            @foreach($desas as $d)
                                <option value="{{ $d->id }}" {{ old('desa_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }} ({{ $d->kecamatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="instansi" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Instansi / Unit Kerja / Posyandu
                    </label>
                    <input id="instansi" name="instansi" type="text" value="{{ old('instansi') }}" list="instansi-register-options"
                           placeholder="Contoh: Puskesmas Cisalak, Posyandu Melati 01, Pustu..."
                           class="mt-1 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    <datalist id="instansi-register-options">
                        <option value="Puskesmas"></option>
                        <option value="Puskesmas Pembantu (Pustu)"></option>
                        <option value="Poskesdes"></option>
                        <option value="Polindes"></option>
                        <option value="Posyandu"></option>
                        <option value="Klinik Pratama"></option>
                        <option value="Praktik Mandiri Bidan (PMB)"></option>
                    </datalist>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Nama Puskesmas, Posyandu, atau fasilitas kesehatan tempat bertugas</span>
                </div>

                <!-- GPS Location Picker & Deteksi Google (Desa, Kecamatan, Kabupaten) -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-slate-800">Lokasi Penugasan (Diambil dari Google)</span>
                            <span class="text-[10px] text-slate-400 block">Otomatis mendeteksi Desa, Kecamatan, dan Kabupaten</span>
                        </div>
                        <button type="button" @click="getLocationGoogle()" :disabled="gettingLocation"
                                class="text-xs font-bold text-teal-800 hover:text-teal-900 flex items-center gap-1.5 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-xl border border-teal-200 shadow-2xs transition-colors disabled:opacity-50">
                            <svg class="w-3.5 h-3.5 text-teal-700" :class="{ 'animate-spin': gettingLocation }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span x-text="gettingLocation ? 'Mendeteksi...' : '📍 Ambil via Google GPS'"></span>
                        </button>
                    </div>

                    <!-- Input Cari Wilayah Google -->
                    <div class="flex gap-2">
                        <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchGoogleLocation()"
                               placeholder="Cari desa/kelurahan atau kecamatan..."
                               class="flex-1 px-3 py-1.5 rounded-xl border border-slate-300 text-xs bg-white" />
                        <button type="button" @click="searchGoogleLocation()" :disabled="searchLoading"
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shrink-0 disabled:opacity-50">
                            <span x-text="searchLoading ? '...' : 'Cari Google'"></span>
                        </button>
                    </div>

                    <!-- Hasil Google Card -->
                    <div x-show="detectedGoogle" x-cloak class="p-3 rounded-xl bg-teal-50/80 border border-teal-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-teal-900 text-[11px]">📍 Lokasi Terdeteksi Google:</span>
                            <template x-if="detectedGoogle?.matched_desa_nama">
                                <span class="text-[10px] text-emerald-800 font-bold bg-white px-2 py-0.5 rounded-md border border-emerald-300">
                                    ✓ Sinkron ke <span x-text="detectedGoogle?.matched_desa_nama"></span>
                                </span>
                            </template>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-[11px]">
                            <div class="bg-white p-2 rounded-lg border border-teal-100">
                                <span class="text-slate-400 block text-[9px] uppercase font-bold">Desa/Kel.</span>
                                <span class="font-bold text-teal-900 block truncate" x-text="detectedGoogle?.desa || '-'"></span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-teal-100">
                                <span class="text-slate-400 block text-[9px] uppercase font-bold">Kecamatan</span>
                                <span class="font-bold text-slate-800 block truncate" x-text="detectedGoogle?.kecamatan || '-'"></span>
                            </div>
                            <div class="bg-white p-2 rounded-lg border border-teal-100">
                                <span class="text-slate-400 block text-[9px] uppercase font-bold">Kabupaten</span>
                                <span class="font-bold text-slate-800 block truncate" x-text="detectedGoogle?.kabupaten || '-'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-500">Latitude</label>
                            <input type="text" name="latitude" x-model="lat" placeholder="-6.589123"
                                   class="mt-0.5 block w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-mono" />
                        </div>
                        <div>
                            <label class="block text-[10px] uppercase font-semibold text-slate-500">Longitude</label>
                            <input type="text" name="longitude" x-model="lng" placeholder="106.789123"
                                   class="mt-0.5 block w-full px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-mono" />
                        </div>
                    </div>
                </div>

                <x-password-fields :required="true" />

                <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200/80 text-amber-900 text-xs flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Setelah mendaftar, akun Anda akan diverifikasi langsung oleh Admin Puskesmas / Dinas Kesehatan sebelum dapat digunakan untuk masuk ke sistem.</span>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all">
                        Daftar Akun (Ajukan Verifikasi ke Admin)
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <span class="text-xs text-slate-500">Sudah memiliki akun?</span>
                <a href="{{ route('login') }}" class="text-xs font-bold text-teal-700 hover:text-teal-800 ml-1">
                    Masuk di sini →
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

