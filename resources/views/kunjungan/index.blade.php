@extends('layouts.app', ['title' => 'Catatan Kunjungan ANC & Jemput Bola', 'header' => 'Pencatatan Kunjungan ANC & Jemput Bola'])

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        activeKunjunganId: null,
        activeNama: '',
        activeJenis: '',
        activeMetode: 'jemput_bola',
        activeTanggal: '',
        activeCatatan: '',
        activeHadir: true,
        activeDilayani: true,
        activeTb: '',
        activeBb: '',
        activeSistol: '',
        activeDiastol: '',
        activeLila: '',
        activeTfu: '',
        activeDjj: '',
        activeHb: '',
        activeKepatuhanFe: true,
        activeBentuk: '',
        activeJadwalBerikutnya: '',
        activeHasSkrining: false,
        activeBumilId: null,
        openConfirmModal(id, nama, jenis, metode, tanggal, catatan = '', hadir = true, dilayani = true, tb = '', bb = '', sistol = '', diastol = '', lila = '', tfu = '', djj = '', hb = '', kepatuhanFe = true, bentuk = '', jadwalBerikutnya = '', hasSkrining = false, bumilId = null) {
            this.activeKunjunganId = id;
            this.activeNama = nama;
            this.activeJenis = jenis;
            this.activeMetode = metode || 'jemput_bola';
            this.activeTanggal = tanggal;
            this.activeCatatan = catatan;
            this.activeHadir = Boolean(hadir);
            this.activeDilayani = Boolean(dilayani);
            this.activeTb = tb;
            this.activeBb = bb;
            this.activeSistol = sistol;
            this.activeDiastol = diastol;
            this.activeLila = lila;
            this.activeTfu = tfu;
            this.activeDjj = djj;
            this.activeHb = hb;
            this.activeKepatuhanFe = Boolean(kepatuhanFe);
            this.activeBentuk = bentuk;
            this.activeJadwalBerikutnya = jadwalBerikutnya;
            this.activeHasSkrining = Boolean(hasSkrining);
            this.activeBumilId = bumilId;
            this.modalOpen = true;
        }
    }">

        <!-- Header Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800">
                        Model Jemput Bola ANC
                    </span>
                    <span class="text-xs text-slate-500">Pendampingan Kader Lintas Daerah</span>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Kunjungan & Pendampingan ANC</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status kehadiran, metode kunjungan rumah/posyandu, dan
                    pelayanan medis ibu hamil</p>
            </div>

            <a href="{{ route('kunjungan.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 active:scale-95 text-white text-xs font-bold shadow-sm shadow-teal-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Catat Kunjungan / Jemput Bola</span>
            </a>
        </div>

        <!-- Filter Bar & Grouping Tabs -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">

            <!-- Territory Grouping Navigation Tabs -->
            @if (auth()->user()->isAdminPuskesmas())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Wilayah Puskesmas:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Kunjungan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200/80' }}">
                        <span>🏥 Wilayah Puskesmas Kita</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/80' }}">
                        <span>🌐 Luar Wilayah / Lintas Faskes</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Domisili Warga:</span>
                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'semua', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>📋 Semua Kunjungan</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $countSemua }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'dalam', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-teal-600 text-white shadow-xs' : 'bg-teal-50 text-teal-800 hover:bg-teal-100 border border-teal-200/80' }}">
                        <span>🏡 Warga Desa Kita ({{ auth()->user()->desa?->nama ?? 'Binaan' }})</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'dalam' ? 'bg-white/20 text-white' : 'bg-teal-200 text-teal-900' }}">{{ $countDalam }}</span>
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['kelompok' => 'luar', 'page' => 1]) }}"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200/80' }}">
                        <span>🌐 Warga Luar Daerah / Lintas Desa</span>
                        <span
                            class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($currentKelompok ?? 'semua') === 'luar' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-900' }}">{{ $countLuar }}</span>
                    </a>
                </div>
            @endif

            <form method="GET" action="{{ route('kunjungan.index') }}" class="space-y-3">
                <input type="hidden" name="kelompok" value="{{ request('kelompok', $currentKelompok ?? 'semua') }}" />

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <!-- Search Input -->
                    <div class="relative sm:col-span-2 lg:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama ibu hamil, NIK 16 digit, suami..."
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2.5 pl-9 pr-8 focus:bg-white focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    @if (auth()->user()->isAdmin())
                        <div>
                            <select name="desa_id" onchange="this.form.submit()"
                                class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                                <option value="">-- Semua Wilayah Desa --</option>
                                @if (isset($puskesmasDesas) && $puskesmasDesas->count() > 0 && auth()->user()->isAdminPuskesmas())
                                    <optgroup
                                        label="📍 Desa Wilayah Kerja Puskesmas ({{ auth()->user()->puskesmas_kerja ?? 'UPT' }})">
                                        @foreach ($puskesmasDesas as $d)
                                            <option value="{{ $d->id }}"
                                                {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                {{ $d->nama }} ({{ $d->kecamatan }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                @if (isset($otherDesas) && $otherDesas->count() > 0 && auth()->user()->isAdminPuskesmas())
                                    <optgroup label="🌐 Desa Luar Wilayah / Lintas Faskes">
                                        @foreach ($otherDesas as $d)
                                            <option value="{{ $d->id }}"
                                                {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                                {{ $d->nama }} ({{ $d->puskesmas ?? 'Luar Faskes' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @elseif (!auth()->user()->isAdminPuskesmas())
                                    @foreach ($desas as $d)
                                        <option value="{{ $d->id }}"
                                            {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                            {{ $d->nama }} ({{ $d->puskesmas ?? 'Puskesmas' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif

                    <div>
                        <select name="metode" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Metode --</option>
                            <option value="jemput_bola" {{ request('metode') === 'jemput_bola' ? 'selected' : '' }}>🏠
                                Jemput Bola (Rumah)</option>
                            <option value="posyandu" {{ request('metode') === 'posyandu' ? 'selected' : '' }}>👥 Posyandu
                            </option>
                            <option value="puskesmas" {{ request('metode') === 'puskesmas' ? 'selected' : '' }}>🏥
                                Puskesmas / Faskes</option>
                        </select>
                    </div>

                    <div>
                        <select name="jenis" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Tahap (K1 - K6) --</option>
                            @foreach (['K1', 'K2', 'K3', 'K4', 'K5', 'K6'] as $k)
                                <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>
                                    Kunjungan {{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="status" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2.5 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Status --</option>
                            <option value="belum_hadir" {{ request('status') === 'belum_hadir' ? 'selected' : '' }}>Belum
                                Hadir / Terjadwal</option>
                            <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir / Selesai
                                Kunjungan</option>
                            <option value="dilayani" {{ request('status') === 'dilayani' ? 'selected' : '' }}>Dilayani
                                Lengkap</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <div class="text-[11px] text-slate-500">
                        Menampilkan <strong class="text-slate-800">{{ $kunjungans->total() }}</strong> catatan kunjungan
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs">
                            Terapkan Filter
                        </button>
                        @if (request()->hasAny(['search', 'desa_id', 'metode', 'jenis', 'status', 'kelompok', 'scope']))
                            <a href="{{ route('kunjungan.index') }}"
                                class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                                Reset Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Kunjungan Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal & Metode</th>
                            <th class="py-3 px-4">Nama Ibu Hamil</th>
                            <th class="py-3 px-4">Wilayah / Puskesmas</th>
                            <th class="py-3 px-4 text-center">Tahap ANC</th>
                            <th class="py-3 px-4">Pemeriksaan Fisik</th>
                            <th class="py-3 px-4 text-center">Jadwal Berikutnya</th>
                            <th class="py-3 px-4 text-center">Status Kehadiran</th>
                            <th class="py-3 px-4 text-center">Status Pelayanan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kunjungans as $k)
                            @php
                                $hasSkriningKunjungan = $k->skrining_id !== null || $k->ibuHamil->skrinings()->exists();
                                $tbVal = $k->tinggi_badan ?? ($k->ibuHamil->tinggi_badan ?? '');
                                $bbVal = $k->berat_badan ?? ($k->ibuHamil->berat_badan ?? '');
                                $sistolVal = $k->tekanan_darah_sistol ?? ($k->ibuHamil->tekanan_darah_sistol ?? '');
                                $diastolVal = $k->tekanan_darah_diastol ?? ($k->ibuHamil->tekanan_darah_diastol ?? '');
                                $lilaVal = $k->lila ?? ($k->ibuHamil->lila ?? '');
                                $tfuVal = $k->tfu ?? '';
                                $djjVal = $k->djj ?? '';
                                $hbVal = $k->hb ?? ($k->ibuHamil->hemoglobin ?? '');
                                $kepatuhanFeVal = $k->kepatuhan_fe ? 1 : 0;
                                $jadwalNextVal = $k->jadwal_kunjungan_berikutnya
                                    ? $k->jadwal_kunjungan_berikutnya->format('Y-m-d')
                                    : '';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">
                                        {{ $k->tanggal_kunjungan->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        @if ($k->metode_kunjungan === 'jemput_bola')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                🏠 Jemput Bola
                                            </span>
                                        @elseif ($k->metode_kunjungan === 'puskesmas')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                                🏥 Puskesmas
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                                                👥 Posyandu
                                            </span>
                                        @endif
                                        @if ($k->foto_pendampingan)
                                            <a href="{{ $k->foto_pendampingan_url }}" target="_blank"
                                                title="Lihat Foto Dokumentasi"
                                                class="text-teal-600 hover:text-teal-800 text-[11px] font-medium inline-flex items-center">
                                                📷
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col">
                                        <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}"
                                            class="font-bold text-slate-800 hover:text-teal-700 text-xs sm:text-sm">
                                            {{ $k->ibuHamil->nama }}
                                        </a>
                                        <span class="block font-mono text-[11px] text-slate-500 font-semibold mt-0.5">
                                            NIK: {{ $k->ibuHamil->nik ?? '-' }}
                                        </span>

                                        <!-- Badge Pengelompokan Wilayah / Domisili Warga -->
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                            @if (auth()->user()->isAdminPuskesmas())
                                                @if (in_array($k->ibuHamil->desa_id, $puskesmasDesaIds ?? []))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                        Wilayah Puskesmas Kita
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                        Luar Wilayah Puskesmas
                                                    </span>
                                                @endif
                                            @elseif (auth()->user()->isBidan() || auth()->user()->isKader())
                                                @if ($k->ibuHamil->desa_id === auth()->user()->desa_id)
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                                                        Warga Desa Tercover
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                                        Warga Luar Daerah
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="font-medium text-slate-800">{{ $k->desa?->nama }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $k->desa?->puskesmas ?? 'Puskesmas' }}
                                    </div>
                                    @if ($k->desa_id !== $k->ibuHamil->desa_id)
                                        <div class="mt-1">
                                            <span
                                                class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200"
                                                title="Pasien terdaftar di {{ $k->ibuHamil->desa?->nama }} ({{ $k->ibuHamil->desa?->puskesmas ?? 'Puskesmas Asal' }})">
                                                🌐 Asal: {{ $k->ibuHamil->desa?->nama }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-xl font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $k->tahap_anc ?? $k->jenis_kunjungan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="flex flex-col gap-0.5 text-[11px]">
                                        <span><strong class="text-slate-800">TB/BB:</strong>
                                            {{ $tbVal ? $tbVal . ' cm' : '-' }} /
                                            {{ $bbVal ? $bbVal . ' kg' : '-' }}</span>
                                        <span><strong class="text-slate-800">TD:</strong>
                                            {{ $sistolVal && $diastolVal ? "{$sistolVal}/{$diastolVal} mmHg" : '-' }}</span>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                            @if ($lilaVal)
                                                <span>LiLA: <strong class="text-slate-700">{{ $lilaVal }}
                                                        cm</strong></span>
                                            @endif
                                            @if ($hbVal)
                                                <span>Hb: <strong class="text-slate-700">{{ $hbVal }}
                                                        g/dL</strong></span>
                                            @endif
                                            @if ($k->kepatuhan_fe)
                                                <span class="text-emerald-700 font-bold" title="Patuh Tablet Fe">✓
                                                    Fe</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->jadwal_kunjungan_berikutnya)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            🗓️ {{ $k->jadwal_kunjungan_berikutnya->translatedFormat('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_hadir)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            ✓ Hadir
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            ⏳ Belum Hadir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_dilayani)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✓ Dilayani
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                            Belum Dilayani
                                        </span>
                                        @if (!$hasSkriningKunjungan)
                                            <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id]) }}"
                                                class="block text-[10px] text-teal-700 font-bold hover:underline mt-1">
                                                + Isi Skrining SPR
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if (!$k->status_hadir)
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $hasSkriningKunjungan ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', '{{ $lilaVal }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $hbVal }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-[11px] shadow-xs transition-all">
                                                <span>Konfirmasi</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->metode_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', '{{ $lilaVal }}', '{{ $tfuVal }}', '{{ $djjVal }}', '{{ $hbVal }}', {{ $kepatuhanFeVal }}, '{{ addslashes($k->bentuk_pendampingan ?? '') }}', '{{ $jadwalNextVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200 transition-all">
                                                <span>Ubah</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('kunjungan.edit', $k) }}"
                                            class="text-blue-600 hover:text-blue-700 font-semibold text-xs ml-1">
                                            Edit
                                        </a>

                                        @if (!auth()->user()->isKader())
                                            <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                                onsubmit="return confirm('Hapus catatan kunjungan ini?');"
                                                class="inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    Belum ada data catatan kunjungan ANC.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($kunjungans->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $kunjungans->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Konfirmasi Kehadiran & Pelayanan Lengkap -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200/80">

                    <form :action="'{{ url('/kunjungan') }}/' + activeKunjunganId + '/konfirmasi'" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-teal-600 to-emerald-600 p-5 text-white">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider"
                                    x-text="'ANC ' + activeJenis"></span>
                                <button type="button" @click="modalOpen = false"
                                    class="text-white/80 hover:text-white text-base">✕</button>
                            </div>
                            <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                            <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong class="text-white font-bold"
                                    x-text="activeNama"></strong></p>
                        </div>

                        <div class="p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                            <!-- Tanggal Kehadiran Realisasi -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_kunjungan" x-model="activeTanggal" required
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <!-- Pemeriksaan Fisik: TB, BB, TD -->
                            <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik Kunjungan
                                    (10T):</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TB
                                            (cm)</label>
                                        <input type="number" step="0.1" name="tinggi_badan" x-model="activeTb"
                                            placeholder="158"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">BB
                                            (kg)</label>
                                        <input type="number" step="0.1" name="berat_badan" x-model="activeBb"
                                            placeholder="56"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                            Sistol</label>
                                        <input type="number" name="tekanan_darah_sistol" x-model="activeSistol"
                                            placeholder="120"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                            Diastol</label>
                                        <input type="number" name="tekanan_darah_diastol" x-model="activeDiastol"
                                            placeholder="80"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs pt-2 border-t border-teal-200/60">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">LiLA
                                            (cm)</label>
                                        <input type="number" step="0.1" name="lila" x-model="activeLila"
                                            placeholder="24"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TFU
                                            (cm)</label>
                                        <input type="number" step="0.1" name="tfu" x-model="activeTfu"
                                            placeholder="26"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">DJJ
                                            (dpm)</label>
                                        <input type="number" name="djj" x-model="activeDjj" placeholder="140"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">Hb
                                            (g/dL)</label>
                                        <input type="number" step="0.1" name="hb" x-model="activeHb"
                                            placeholder="11.5"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div class="pt-1.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="kepatuhan_fe" value="1"
                                            x-model="activeKepatuhanFe"
                                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                                        <span class="text-xs font-semibold text-slate-800">
                                            Rutin Mengonsumsi Tablet Fe
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Jadwal Kunjungan Berikutnya -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Jadwal Kunjungan / Jemput Bola Berikutnya
                                </label>
                                <input type="date" name="jadwal_kunjungan_berikutnya" x-model="activeJadwalBerikutnya"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <!-- Status Checkboxes & Skrining SPR Link -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="hidden" name="status_hadir" value="0" />
                                    <input type="checkbox" name="status_hadir" value="1" x-model="activeHadir"
                                        class="mt-0.5 h-4 w-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer" />
                                    <div>
                                        <strong class="text-slate-800 block text-xs">Ibu Hamil Hadir / Selesai
                                            Didampingi</strong>
                                        <span class="text-[11px] text-slate-500">Tandai jika ibu hamil berhasil dikunjungi
                                            atau hadir</span>
                                    </div>
                                </label>

                                <div class="pt-2.5 border-t border-slate-200">
                                    <template x-if="activeHasSkrining">
                                        <label class="flex items-start gap-3 cursor-pointer"
                                            :class="!activeHadir ? 'opacity-50 pointer-events-none' : ''">
                                            <input type="hidden" name="status_dilayani" value="0" />
                                            <input type="checkbox" name="status_dilayani" value="1"
                                                x-model="activeDilayani" :disabled="!activeHadir"
                                                class="mt-0.5 h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer" />
                                            <div>
                                                <strong class="text-slate-800 block text-xs">Pelayanan Medis Lengkap (Telah
                                                    Dilayani)</strong>
                                                <span class="text-[11px] text-emerald-700 font-medium">✓ Skrining SPR
                                                    Terpenuhi</span>
                                            </div>
                                        </label>
                                    </template>
                                    <template x-if="!activeHasSkrining">
                                        <div
                                            class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex flex-col gap-2">
                                            <div>
                                                <strong class="block">⚠️ Skrining SPR Belum Diisi</strong>
                                                <span class="text-[11px] text-amber-700">Status Dilayani hanya dapat
                                                    dicatat setelah ibu hamil mengisi kuesioner Skrining SPR.</span>
                                            </div>
                                            <div>
                                                <a :href="'{{ url('/skrining/create') }}?ibu_hamil_id=' + activeBumilId +
                                                    '&kunjungan_id=' + activeKunjunganId"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                                    <span>+ Isi Skrining SPR Sekarang (Otomatis Dilayani) →</span>
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Catatan Pemeriksaan / Pendampingan (Opsional)
                                </label>
                                <textarea name="catatan" x-model="activeCatatan" rows="2"
                                    placeholder="Contoh: Pendampingan jemput bola lancar, minum Fe tertib..."
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Konfirmasi</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
@endsection
