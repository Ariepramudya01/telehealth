@extends('layouts.app', ['title' => 'Catat Kunjungan ANC & Jemput Bola', 'header' => 'Catat Kunjungan ANC & Jemput Bola'])

@section('content')
<div class="max-w-3xl mx-auto" x-data="{
    ibuHamils: @js($ibuHamilsData),
    selectedId: '{{ old('ibu_hamil_id', $selectedIbuHamilId) }}',
    searchBumil: '',
    filterTab: 'semua',
    isPickerOpen: false,
    metode: '{{ old('metode_kunjungan', 'jemput_bola') }}',
    tahapAnc: '{{ old('tahap_anc', old('jenis_kunjungan', 'K1')) }}',
    tb: '{{ old('tinggi_badan') }}',
    bb: '{{ old('berat_badan') }}',
    sistol: '{{ old('tekanan_darah_sistol') }}',
    diastol: '{{ old('tekanan_darah_diastol') }}',
    lila: '{{ old('lila') }}',
    hb: '{{ old('hb') }}',
    hasSkrining: false,
    statusHadir: true,
    statusDilayani: false,
    isLintasDaerah: false,
    selectedBumilDesa: '',
    selectedBumilPuskesmas: '',
    fotoPreview: null,
    get selectedBumil() {
        return this.ibuHamils.find(b => String(b.id) === String(this.selectedId)) || null;
    },
    get filteredBumils() {
        const q = (this.searchBumil || '').toLowerCase().trim();
        return this.ibuHamils.filter(b => {
            if (this.filterTab === 'sendiri' && b.is_lintas_daerah) return false;
            if (this.filterTab === 'lintas' && !b.is_lintas_daerah) return false;
            if (!q) return true;
            const nama = (b.nama || '').toLowerCase();
            const nik = (b.nik || '').toLowerCase();
            const desa = (b.desa_nama || '').toLowerCase();
            const pkm = (b.puskesmas || '').toLowerCase();
            const suami = (b.nama_suami || '').toLowerCase();
            return nama.includes(q) || nik.includes(q) || desa.includes(q) || pkm.includes(q) || suami.includes(q);
        });
    },
    selectBumil(id) {
        this.selectedId = id;
        this.isPickerOpen = false;
        this.searchBumil = '';
        this.onBumilChange();
    },
    openPicker() {
        this.isPickerOpen = true;
        this.searchBumil = '';
        this.$nextTick(() => {
            const input = document.getElementById('search-bumil-input');
            if (input) input.focus();
        });
    },
    init() {
        if (this.selectedId) {
            this.onBumilChange();
            this.isPickerOpen = false;
        } else {
            this.isPickerOpen = true;
        }
    },
    onBumilChange() {
        const found = this.ibuHamils.find(b => b.id == this.selectedId);
        if (found) {
            this.tb = found.tinggi_badan || '';
            this.bb = '';
            this.sistol = '';
            this.diastol = '';
            this.lila = '';
            this.hb = '';
            this.hasSkrining = false;
            this.statusDilayani = false;
            this.isLintasDaerah = Boolean(found.is_lintas_daerah);
            this.selectedBumilDesa = found.desa_nama || '';
            this.selectedBumilPuskesmas = found.puskesmas || '';
        } else {
            this.hasSkrining = false;
            this.statusDilayani = false;
            this.isLintasDaerah = false;
            this.selectedBumilDesa = '';
            this.selectedBumilPuskesmas = '';
        }
    },
    previewImage(e) {
        const file = e.target.files[0];
        if (file) {
            this.fotoPreview = URL.createObjectURL(file);
        } else {
            this.fotoPreview = null;
        }
    },
    get imt() {
        if (!this.tb || !this.bb || this.tb <= 0) return null;
        const m = this.tb / 100;
        return (this.bb / (m * m)).toFixed(1);
    },
    get kategoriGizi() {
        const v = parseFloat(this.imt);
        if (isNaN(v)) return '-';
        if (v < 18.5) return 'Gizi Kurang (Underweight)';
        if (v <= 24.9) return 'Gizi Baik (Normal)';
        if (v <= 29.9) return 'Gizi Lebih (Overweight)';
        return 'Obesitas';
    }
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('kunjungan.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Data Kunjungan
            </a>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800">
                    Jemput Bola & ANC
                </span>
                <span class="text-xs text-slate-500">Kunjungan Rumah & Faskes</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mt-1">Pencatatan Kunjungan & Pendampingan ANC</h2>
            <p class="text-xs text-slate-500">Catat pemeriksaan fisik, pendampingan jemput bola, konsumsi Fe, dan jadwal kunjungan berikutnya</p>
        </div>


        <form action="{{ route('kunjungan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- 1. Pemilihan Ibu Hamil (Smart Search & Picker Cepat) -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Pilih Ibu Hamil <span class="text-rose-500">*</span>
                    </label>
                    <template x-if="selectedBumil">
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                            ✓ Ibu Hamil Terpilih
                        </span>
                    </template>
                </div>

                <!-- Hidden Native Select (Memastikan Integrasi POST Form & Kompatibilitas Sistem Tetap 100% Valid) -->
                <select id="ibu_hamil_id" name="ibu_hamil_id" required
                        x-model="selectedId"
                        @change="onBumilChange()"
                        class="sr-only pointer-events-none"
                        tabindex="-1"
                        aria-hidden="true">
                    <option value="">-- Pilih Ibu Hamil --</option>
                    @if($ibuHamilsSendiri->isNotEmpty())
                        <optgroup label="Wilayah Sendiri (Desa {{ auth()->user()->desa?->nama ?? 'Wilayah Kerja' }})">
                            @foreach($ibuHamilsSendiri as $bumil)
                                <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                    {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - {{ $bumil->desa?->nama }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    @if($ibuHamilsLintas->isNotEmpty())
                        <optgroup label="🏥 Desa / Faskes Lain di Wilayah Kabupaten">
                            @foreach($ibuHamilsLintas as $bumil)
                                <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                    🏥 {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - Asal: {{ $bumil->desa?->nama }} ({{ $bumil->desa?->puskesmas ?? 'Puskesmas Luar' }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>

                <!-- STATE A: KARTU IBU HAMIL TERPILIH (JELAS, MODERN, & MUDAH DIGANTI) -->
                <template x-if="selectedBumil && !isPickerOpen">
                    <div class="relative bg-gradient-to-br from-teal-50/90 via-emerald-50/40 to-white rounded-3xl border-2 border-teal-500/30 p-4 sm:p-5 shadow-xs transition-all hover:border-teal-500/60">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-start sm:items-center gap-3.5">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-600 to-emerald-600 text-white flex items-center justify-center font-black text-lg shadow-sm shrink-0">
                                    <span x-text="selectedBumil.nama ? selectedBumil.nama.charAt(0).toUpperCase() : '👩'"></span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base font-extrabold text-slate-900" x-text="selectedBumil.nama"></h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                            NIK: <span x-text="selectedBumil.nik || '-'"></span>
                                        </span>
                                        <template x-if="selectedBumil.is_lintas_daerah">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                🏥 Lintas Faskes
                                            </span>
                                        </template>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-2 flex-wrap">
                                        <span>📍 <strong class="text-slate-800" x-text="selectedBumil.desa_nama"></strong> (<span x-text="selectedBumil.puskesmas"></span>)</span>
                                        <template x-if="selectedBumil.usia">
                                            <span>• Usia: <strong class="text-slate-700" x-text="selectedBumil.usia + ' th'"></strong></span>
                                        </template>
                                        <template x-if="selectedBumil.nama_suami">
                                            <span>• Suami: <strong class="text-slate-700" x-text="selectedBumil.nama_suami"></strong></span>
                                        </template>
                                        <template x-if="selectedBumil.tinggi_badan">
                                            <span>• TB Baseline: <strong class="text-slate-700" x-text="selectedBumil.tinggi_badan + ' cm'"></strong></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Ganti Ibu Hamil -->
                            <button type="button" 
                                @click="openPicker()"
                                class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-teal-800 border border-teal-200/90 text-xs font-bold shadow-2xs transition-all active:scale-95 cursor-pointer shrink-0">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span>Ganti Ibu Hamil</span>
                            </button>
                        </div>

                        <!-- Antar Faskes Info Alert -->
                        <template x-if="isLintasDaerah">
                            <div class="mt-3.5 p-3 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs flex items-start gap-2.5 shadow-2xs">
                                <span class="text-base shrink-0">🏥</span>
                                <div class="flex-1">
                                    <span class="font-bold block text-sky-900">Pelayanan Antar-Desa / Puskesmas</span>
                                    <span class="text-[11px] text-sky-700 block mt-0.5">
                                        Pasien terdaftar di Desa <strong x-text="selectedBumilDesa"></strong> (<span x-text="selectedBumilPuskesmas"></span>). Pelayanan ini dicatat oleh faskes <strong>{{ auth()->user()->desa?->nama ?? 'faskes Anda' }}</strong> dan otomatis terhubung ke rekam medis ibu.
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- STATE B: MODE PENCARIAN & PEMILIH IBU HAMIL (LIVE SEARCH, TABS, QUICK CLICK) -->
                <template x-if="!selectedBumil || isPickerOpen">
                    <div class="bg-white rounded-3xl border-2 border-teal-500/40 p-4 sm:p-5 shadow-xs space-y-3.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-teal-600 animate-pulse"></span>
                                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Cari & Pilih Ibu Hamil</span>
                            </div>
                            <template x-if="selectedBumil">
                                <button type="button" @click="isPickerOpen = false" class="text-xs text-slate-500 hover:text-slate-800 font-semibold cursor-pointer">
                                    ✕ Batal Ganti
                                </button>
                            </template>
                        </div>

                        <!-- Live Search Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text"
                                id="search-bumil-input"
                                x-model="searchBumil"
                                placeholder="Ketik Nama Ibu, NIK, Desa, atau Nama Suami..."
                                class="w-full pl-10 pr-9 py-2.5 rounded-2xl border border-slate-300 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all shadow-inner">
                            <button type="button" 
                                x-show="searchBumil.length > 0" 
                                @click="searchBumil = ''" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                ✕
                            </button>
                        </div>

                        <!-- Filter Quick Tabs -->
                        <div class="flex items-center gap-1.5 flex-wrap text-xs">
                            <button type="button" 
                                @click="filterTab = 'semua'"
                                :class="filterTab === 'semua' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                                ✨ Semua (<span x-text="ibuHamils.length"></span>)
                            </button>
                            <button type="button" 
                                @click="filterTab = 'sendiri'"
                                :class="filterTab === 'sendiri' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                                🏡 Wilayah {{ auth()->user()->isAdminPuskesmas() ? 'Puskesmas' : 'Desa' }} (<span x-text="ibuHamils.filter(b => !b.is_lintas_daerah).length"></span>)
                            </button>
                            <template x-if="ibuHamils.some(b => b.is_lintas_daerah)">
                                <button type="button" 
                                    @click="filterTab = 'lintas'"
                                    :class="filterTab === 'lintas' ? 'bg-teal-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                    class="px-3 py-1.5 rounded-xl transition-all cursor-pointer">
                                    🏥 Lintas Faskes (<span x-text="ibuHamils.filter(b => b.is_lintas_daerah).length"></span>)
                                </button>
                            </template>
                        </div>

                        <!-- Filtered Results List (Touch-Friendly & Keyboard Accessible) -->
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 rounded-2xl border border-slate-200/80 bg-slate-50/50 p-1">
                            <template x-for="bumil in filteredBumils" :key="bumil.id">
                                <div @click="selectBumil(bumil.id)"
                                    :class="String(bumil.id) === String(selectedId) ? 'bg-teal-100/70 border-teal-300' : 'hover:bg-white hover:border-slate-200'"
                                    class="p-3 rounded-xl border border-transparent transition-all cursor-pointer flex items-center justify-between gap-3 group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-800 group-hover:bg-teal-600 group-hover:text-white flex items-center justify-center font-bold text-xs transition-colors shrink-0">
                                            <span x-text="bumil.nama ? bumil.nama.charAt(0).toUpperCase() : '👩'"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-teal-700 transition-colors" x-text="bumil.nama"></span>
                                                <span class="font-mono text-[10px] text-slate-600 bg-slate-200/70 px-1.5 py-0.5 rounded" x-text="'NIK: ' + (bumil.nik || '-')"></span>
                                                <template x-if="bumil.is_lintas_daerah">
                                                    <span class="text-[9px] font-bold bg-sky-100 text-sky-800 px-1.5 py-0.2 rounded">
                                                        🏥 Lintas Faskes
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                <span>📍 <span x-text="bumil.desa_nama"></span> (<span x-text="bumil.puskesmas"></span>)</span>
                                                <template x-if="bumil.usia">
                                                    <span>• Usia: <span x-text="bumil.usia + ' th'"></span></span>
                                                </template>
                                                <template x-if="bumil.nama_suami">
                                                    <span>• Suami: <span x-text="bumil.nama_suami"></span></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shrink-0 cursor-pointer"
                                        :class="String(bumil.id) === String(selectedId) ? 'bg-teal-600 text-white' : 'bg-white group-hover:bg-teal-600 group-hover:text-white text-teal-800 border border-slate-200 shadow-2xs'">
                                        <span x-text="String(bumil.id) === String(selectedId) ? '✓ Terpilih' : 'Pilih'"></span>
                                        <span x-show="String(bumil.id) !== String(selectedId)">→</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="filteredBumils.length === 0">
                                <div class="p-8 text-center text-slate-400 text-xs">
                                    <div class="text-2xl mb-1">🔍</div>
                                    <p class="font-semibold text-slate-600">Tidak ada ibu hamil yang cocok.</p>
                                    <p class="text-[11px] mt-0.5">Coba gunakan kata kunci pencarian nama atau NIK yang lain.</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 2. Metode Kunjungan (Jemput Bola / Posyandu / Puskesmas) -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">
                    Metode Kunjungan / Pendampingan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'jemput_bola' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="jemput_bola" x-model="metode" class="sr-only" required />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'jemput_bola' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                🏠
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'jemput_bola' ? 'text-teal-900' : 'text-slate-800'">
                                    Jemput Bola
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Kunjungan Rumah / Kader</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'posyandu' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="posyandu" x-model="metode" class="sr-only" />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'posyandu' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                👥
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'posyandu' ? 'text-teal-900' : 'text-slate-800'">
                                    Posyandu
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Pos Layanan Terpadu Desa</span>
                            </div>
                        </div>
                    </label>

                    <label class="relative flex items-center p-3 rounded-2xl border cursor-pointer transition-all"
                           :class="metode === 'puskesmas' ? 'border-teal-500 bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                        <input type="radio" name="metode_kunjungan" value="puskesmas" x-model="metode" class="sr-only" />
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                                  :class="metode === 'puskesmas' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600'">
                                🏥
                            </span>
                            <div>
                                <span class="text-xs font-bold block" :class="metode === 'puskesmas' ? 'text-teal-900' : 'text-slate-800'">
                                    Puskesmas / Faskes
                                </span>
                                <span class="text-[10px] text-slate-500 block leading-tight">Pemeriksaan di Klinik Faskes</span>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Detail Khusus Layanan Jemput Bola -->
                <div x-show="metode === 'jemput_bola'" x-cloak class="mt-3 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🏠</span>
                        <span class="text-xs font-bold text-amber-900">Detail Layanan Jemput Bola (Kunjungan Rumah)</span>
                    </div>

                    <div>
                        <label for="alasan_jemput_bola" class="block text-[11px] font-semibold text-amber-900 uppercase tracking-wider mb-1">
                            Alasan Dilakukan Jemput Bola
                        </label>
                        <select id="alasan_jemput_bola" name="alasan_jemput_bola"
                                class="w-full rounded-xl border border-amber-300 bg-white px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-2xs">
                            <option value="">-- Pilih Alasan Kunjungan Rumah --</option>
                            <option value="mangkir_anc" {{ old('alasan_jemput_bola') === 'mangkir_anc' ? 'selected' : '' }}>⚠️ Mangkir / Terlambat Kunjungan Faskes</option>
                            <option value="risiko_tinggi" {{ old('alasan_jemput_bola') === 'risiko_tinggi' ? 'selected' : '' }}>🚨 Ibu Hamil Risiko Tinggi / Sangat Tinggi (KRT / KRST)</option>
                            <option value="tirah_baring" {{ old('alasan_jemput_bola') === 'tirah_baring' ? 'selected' : '' }}>🛌 Kondisi Lemah / Sakit / Bedrest</option>
                            <option value="kendala_geografis" {{ old('alasan_jemput_bola') === 'kendala_geografis' ? 'selected' : '' }}>🛵 Kendala Geografis / Akses Transportasi Sulit</option>
                            <option value="gizi_anemia" {{ old('alasan_jemput_bola') === 'gizi_anemia' ? 'selected' : '' }}>🥗 Pemantauan Khusus Gizi KEK / Anemia</option>
                            <option value="lainnya" {{ old('alasan_jemput_bola') === 'lainnya' ? 'selected' : '' }}>📋 Pemantauan Rutin Rumah Lainnya</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-amber-200/60">
                        <label class="flex items-center gap-2 cursor-pointer bg-white/80 p-2.5 rounded-xl border border-amber-200 text-xs">
                            <input type="checkbox" name="pendampingan_suami" value="1" {{ old('pendampingan_suami') ? 'checked' : '' }}
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-amber-300 rounded" />
                            <span class="text-[11px] font-semibold text-slate-700">
                                👨‍👩‍👧 Suami / Keluarga Hadir Mendampingi
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer bg-white/80 p-2.5 rounded-xl border border-amber-200 text-xs">
                            <input type="checkbox" name="p4k_siap" value="1" {{ old('p4k_siap') ? 'checked' : '' }}
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-amber-300 rounded" />
                            <span class="text-[11px] font-semibold text-slate-700">
                                📑 Kesiapan P4K & Jalur Rujukan Siap
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 3. Tanggal & Tahap ANC -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal_kunjungan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tanggal Kunjungan & Skrining <span class="text-rose-500">*</span>
                    </label>
                    <div class="mt-1.5 relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-teal-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="text" id="tanggal_kunjungan" name="tanggal_kunjungan" required
                               value="{{ old('tanggal_kunjungan', date('Y-m-d')) }}"
                               placeholder="dd-mm-yyyy"
                               class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium bg-white" />
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Format: Hari-Bulan-Tahun (Contoh: {{ date('d-m-Y') }})</span>
                </div>

                <div>
                    <label for="tahap_anc" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Tahap Kunjungan ANC <span class="text-rose-500">*</span>
                    </label>
                    <select id="tahap_anc" name="tahap_anc" required x-model="tahapAnc"
                            class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                        <option value="K1">K1 (Trimester 1 - Deteksi Awal)</option>
                        <option value="K2">K2 (Trimester 2 - Evaluasi Pertumbuhan)</option>
                        <option value="K3">K3 (Trimester 2 - Pemantauan)</option>
                        <option value="K4">K4 (Trimester 3 - Kesiapan Persalinan)</option>
                        <option value="K5">K5 (Trimester 3 - Evaluasi Akhir)</option>
                        <option value="K6">K6 (Trimester 3 - Siap Persalinan)</option>
                    </select>
                    <!-- Sinkronkan dengan jenis_kunjungan untuk kompatibilitas -->
                    <input type="hidden" name="jenis_kunjungan" :value="tahapAnc" />
                </div>
            </div>

            <!-- 4. Pemeriksaan Fisik & Klinis ANC -->
            <div class="p-4 sm:p-5 rounded-2xl bg-teal-50/50 border border-teal-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-teal-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Pemeriksaan Fisik & Klinis ANC (10T)
                    </span>
                    <span class="text-[11px] text-teal-700 font-medium">Pengukuran Terkini</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TB (cm) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" name="tinggi_badan" x-model="tb" required placeholder="Contoh: 158"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            BB (kg) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" name="berat_badan" x-model="bb" required placeholder="Contoh: 56.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TD Sistol <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tekanan_darah_sistol" x-model="sistol" required placeholder="120"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TD Diastol <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tekanan_darah_diastol" x-model="diastol" required placeholder="80"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                    </div>
                </div>

                <!-- Parameter Tambahan: LiLA, TFU, DJJ, Hb -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-teal-200/60">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            LiLA (cm)
                        </label>
                        <input type="number" step="0.1" name="lila" x-model="lila" placeholder="Contoh: 24.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">&lt; 23.5 cm = Risiko KEK</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            TFU (cm)
                        </label>
                        <input type="number" step="0.1" name="tfu" value="{{ old('tfu') }}" placeholder="Contoh: 26"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Tinggi Fundus Uteri</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            DJJ (dpm)
                        </label>
                        <input type="number" name="djj" value="{{ old('djj') }}" placeholder="Contoh: 140"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal 120-160 dpm</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Hb (g/dL)
                        </label>
                        <input type="number" step="0.1" name="hb" x-model="hb" placeholder="Contoh: 11.5"
                               class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">&lt; 11 g/dL = Anemia</span>
                    </div>
                </div>

                <!-- Kepatuhan Tablet Fe -->
                <div class="pt-2 border-t border-teal-200/60">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="kepatuhan_fe" value="0" />
                        <input type="checkbox" name="kepatuhan_fe" value="1" {{ old('kepatuhan_fe', '1') ? 'checked' : '' }}
                               class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                        <span class="text-xs font-semibold text-slate-800">
                            Ibu Hamil Rutin Minum Tablet Tambah Darah (Fe)
                        </span>
                    </label>
                </div>

                <!-- Preview IMT Otomatis -->
                <template x-if="imt">
                    <div class="mt-2 p-2.5 rounded-xl bg-white border border-teal-200 text-xs flex items-center justify-between">
                        <span class="text-slate-600">
                            IMT Kunjungan Ini: <strong class="text-teal-800 font-bold" x-text="imt + ' kg/m²'"></strong>
                        </span>
                        <span class="font-semibold text-teal-800" x-text="'Status: ' + kategoriGizi"></span>
                    </div>
                </template>
            </div>

            <!-- 5. Pendampingan Kader & Dokumentasi Jemput Bola -->
            <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Bentuk Pendampingan Kader & Dokumentasi
                    </span>
                </div>

                <div>
                    <label for="bentuk_pendampingan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Bentuk Pendampingan yang Diberikan
                    </label>
                    <textarea id="bentuk_pendampingan" name="bentuk_pendampingan" rows="2"
                              placeholder="Misal: Edukasi pemenuhan gizi seimbang, pemantauan konsumsi tablet Fe, pengecekan buku KIA, dan motivasi kehadiran ANC..."
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('bentuk_pendampingan') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="foto_pendampingan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Foto Dokumentasi Jemput Bola
                        </label>
                        <input type="file" id="foto_pendampingan" name="foto_pendampingan" accept="image/*"
                               @change="previewImage($event)"
                               class="mt-1.5 block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer" />
                        <span class="text-[10px] text-slate-400 mt-1 block">Format: JPG, PNG, WEBP (Maksimal 5MB)</span>

                        <!-- Preview Image -->
                        <template x-if="fotoPreview">
                            <div class="mt-2 relative inline-block">
                                <img :src="fotoPreview" alt="Preview Foto" class="w-32 h-24 object-cover rounded-xl border border-slate-200 shadow-xs" />
                                <span class="block text-[10px] text-teal-700 font-semibold mt-1">Preview Foto Terpilih</span>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label for="jadwal_kunjungan_berikutnya" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Jadwal Jemput Bola / Kunjungan Berikutnya
                        </label>
                        <div class="relative mt-1.5">
                            <input type="text" id="jadwal_kunjungan_berikutnya" name="jadwal_kunjungan_berikutnya"
                                   value="{{ old('jadwal_kunjungan_berikutnya') }}"
                                   placeholder="HH-BB-TTTT (contoh: 18-11-2026)"
                                   class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-1 block">Format: Hari-Bulan-Tahun &bull; Akan muncul di pengingat jadwal kader lintas desa</span>
                    </div>
                </div>
            </div>

            <!-- 6. Status Hadir & Dilayani Toggle Box -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <span class="text-xs font-bold text-slate-800 block">Status Pelaksanaan Pemeriksaan & Skrining SPR:</span>

                <div class="flex items-center">
                    <input type="checkbox" id="status_hadir" name="status_hadir" value="1" x-model="statusHadir"
                           class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                    <label for="status_hadir" class="ml-2.5 text-xs font-semibold text-slate-800">
                        Ibu Hamil Berhasil Ditemui / Hadir (Selesai Dikunjungi)
                    </label>
                </div>

                <!-- Status Dilayani Terkoneksi Skrining SPR Kunjungan Ini -->
                <div class="pt-2.5 border-t border-slate-200">
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-amber-950">
                            <span>📋 Skrining SPR Deteksi Dini Per Kunjungan</span>
                        </div>
                        <p class="text-[11px] text-amber-800">
                            Sesuai metode deteksi dini, kuesioner Skrining SPR Poedji Rochjati wajib diisi pada setiap kunjungan (K1 s/d K6) untuk mendeteksi munculnya faktor risiko baru. Status <strong>Dilayani Lengkap</strong> akan otomatis aktif saat formulir Skrining SPR disimpan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 7. Catatan Tambahan -->
            <div>
                <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Catatan Pemeriksaan / Resep / Keluhan (Opsional)
                </label>
                <textarea id="catatan" name="catatan" rows="2"
                          placeholder="Pemberian tablet Fe, saran dokter/bidan, keluhan pasien..."
                          class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('catatan') }}</textarea>
            </div>

            <!-- 8. Tombol Aksi -->
            <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-end gap-2.5">
                <a href="{{ route('kunjungan.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit" name="action" value="save"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition-all">
                    <span>Simpan Kunjungan Saja (Belum Diskrining)</span>
                </button>

                <button type="submit" name="action" value="save_and_screen"
                        class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                    <span>Simpan & Lanjut Skrining SPR Kunjungan Ini →</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
