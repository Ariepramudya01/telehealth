@extends('layouts.app', ['title' => 'Pusat Unduh Laporan', 'header' => 'Pusat Laporan & Ekspor Excel'])

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Pusat Laporan Ekspor Excel (E-Detimil)</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Unduh berkas resmi Microsoft Excel (.xlsx) untuk kebutuhan pelaporan Dinas Kesehatan dan Puskesmas
            </p>
        </div>

        <!-- Scope & Desa Selector -->
        <div>
            @if(auth()->user()->isAdmin())
                <form method="GET" action="{{ route('laporan.index') }}" class="flex items-center gap-2">
                    <label for="desa_id" class="text-xs font-semibold text-slate-600 whitespace-nowrap">Filter Wilayah:</label>
                    <select id="desa_id" name="desa_id" onchange="this.form.submit()" 
                            class="text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Seluruh Wilayah (Semua Desa) --</option>
                        @foreach($desas as $d)
                            <option value="{{ $d->id }}" {{ $selectedDesaId == $d->id ? 'selected' : '' }}>
                                {{ $d->nama }} ({{ $d->kecamatan }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-semibold">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Wilayah Terkunci: {{ auth()->user()->desa?->nama }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Count Summary in Selected Scope -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-xs text-slate-500 block">Ibu Hamil dalam Lingkup</span>
            <span class="text-2xl font-extrabold text-slate-800 mt-1 block">{{ $bumilCount }} Orang</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-xs text-slate-500 block">Total Skrining SPR</span>
            <span class="text-2xl font-extrabold text-teal-700 mt-1 block">{{ $skriningCount }} Sesi</span>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-xs text-slate-500 block">Catatan Kunjungan ANC</span>
            <span class="text-2xl font-extrabold text-blue-700 mt-1 block">{{ $kunjunganCount }} Rekaman</span>
        </div>
    </div>

    <!-- 4 Report Download Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- 1. Laporan Agregat -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-teal-300 transition-all">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">1. Laporan Data Agregat (Rekapitulasi Desa)</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Memuat ringkasan statistik per desa: persentase kehadiran kunjungan (%), persentase dilayani (%), total ibu hamil, dan proporsi tingkat risiko kehamilan (KRR, KRT, KRST).
                </p>
                <div class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-slate-600">
                    <span class="px-2 py-0.5 rounded bg-slate-100">Capaian Hadir %</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Capaian Dilayani %</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Rekap KRR/KRT/KRST</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('laporan.download.agregat') }}?desa_id={{ $selectedDesaId }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Excel Agregat (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 2. Laporan Individu -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-teal-300 transition-all">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">2. Laporan Data Individu (Daftar Ibu Hamil)</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Daftar rinci pasien ibu hamil baris demi baris lengkap dengan NIK, nama suami, no HP, alamat, riwayat obstetri (G-P-A), HPHT, taksiran persalinan, dan skor risiko Poedji Rochjati terkini.
                </p>
                <div class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-slate-600">
                    <span class="px-2 py-0.5 rounded bg-slate-100">Biodata Lengkap</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Gravida / Paritas</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Skor Terakhir</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('laporan.download.individu') }}?desa_id={{ $selectedDesaId }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Excel Individu (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 3. Laporan Kinerja: Data Pribadi Diinput -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-purple-300 transition-all">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">3. Kinerja Petugas: Data Pribadi yang Diinput</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Laporan evaluasi kinerja khusus yang memisahkan data input registrasi ibu hamil berdasarkan petugas (Bidan Desa dan Kader Posyandu) beserta waktu input data.
                </p>
                <div class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-slate-600">
                    <span class="px-2 py-0.5 rounded bg-slate-100">Nama Petugas Input</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Peran Bidan / Kader</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Waktu Registrasi</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('laporan.download.kinerja.input') }}?desa_id={{ $selectedDesaId }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Excel Kinerja Input (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 4. Laporan Kinerja: Data Skrining (Pertanyaan yang Dijawab Terpisah) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-emerald-300 transition-all">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">4. Kinerja Petugas: Jawaban Skrining SPR (Terpisah)</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Laporan kinerja skrining terpisah yang memuat rincian matriks jawaban 23 pertanyaan Poedji Rochjati (APGO, AGO, AGDO), skor per butir, dan total skor dari setiap pemeriksaan yang dilakukan petugas.
                </p>
                <div class="mt-3 flex flex-wrap gap-1.5 text-[11px] text-slate-600">
                    <span class="px-2 py-0.5 rounded bg-slate-100">Matriks 23 Pertanyaan</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Pemberi Jawaban</span>
                    <span class="px-2 py-0.5 rounded bg-slate-100">Kategori Risiko</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('laporan.download.kinerja.skrining') }}?desa_id={{ $selectedDesaId }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Excel Kinerja Skrining (.xlsx)</span>
                </a>
            </div>
        </div>

    </div>

    <!-- Security & Role Notification Banner -->
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-500 flex items-start gap-3">
        <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <div>
            <strong class="font-semibold text-slate-700">Kebijakan Privasi & Pembagian Hak Akses Laporan:</strong>
            Sesuai aturan keamanan data, Admin dapat mengunduh data seluruh desa di wilayah puskesmas. Bidan Desa dibatasi hanya mengunduh laporan sesuai desa penugasannya. Akun Kader tidak memiliki izin untuk mengunduh berkas laporan (namun dapat memantau capaian desanya langsung di Dashboard).
        </div>
    </div>

</div>
@endsection

