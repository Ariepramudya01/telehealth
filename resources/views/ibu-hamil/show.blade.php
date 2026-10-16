@extends('layouts.app', ['title' => 'Rincian Ibu Hamil: ' . $ibuHamil->nama, 'header' => 'Rincian Pasien Ibu Hamil'])

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <a href="{{ route('ibu-hamil.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-1">
                ← Kembali ke Daftar Ibu Hamil
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">{{ $ibuHamil->nama }}</h2>
                @if($latestSkrining)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $latestSkrining->kategori_risiko->badgeClasses() }}">
                        Skor {{ $latestSkrining->total_skor }} • {{ $latestSkrining->kategori_risiko->label() }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                        Belum Pernah Diskrining
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ $ibuHamil->desa?->nama }} • Terdaftar sejak {{ $ibuHamil->created_at->translatedFormat('d F Y') }} oleh {{ $ibuHamil->user?->name }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Skrining SPR Baru</span>
            </a>

            @if(!auth()->user()->isKader())
                <a href="{{ route('kunjungan.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Catat Kunjungan</span>
                </a>
            @endif

            <a href="{{ route('ibu-hamil.edit', $ibuHamil) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <!-- Alert Box for High Risk KRST -->
    @if($latestSkrining && $latestSkrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 flex items-start gap-3 shadow-xs animate-pulse-subtle">
            <svg class="w-6 h-6 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div class="flex-1">
                <h4 class="font-bold text-sm text-rose-900">PERINGATAN KRST (Kehamilan Risiko Sangat Tinggi) - Skor: {{ $latestSkrining->total_skor }}</h4>
                <p class="text-xs text-rose-700 mt-1">
                    Pasien memiliki faktor risiko gawat darurat atau komplikasi obstetri tinggi. Rekomendasi penanganan: Segera lakukan rujukan ke Rumah Sakit dan persalinan harus ditangani oleh Dokter Spesialis Kebidanan (SpOG).
                </p>
            </div>
            <a href="{{ route('skrining.print', $latestSkrining) }}" target="_blank"
               class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs whitespace-nowrap">
                🖨️ Cetak Kartu Rujukan
            </a>
        </div>
    @endif

    <!-- Profile & Obstetric Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Info Pribadi -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-teal-800 border-b border-slate-100 pb-2">
                Identitas Pribadi & Kontak
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Nomor Induk Kependudukan (NIK)</span>
                    <span class="font-mono text-slate-800 text-sm font-semibold">{{ $ibuHamil->nik ?? 'Belum Diisi' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Usia Ibu</span>
                    <span class="font-semibold text-slate-800">
                        {{ $ibuHamil->usia ? $ibuHamil->usia . ' Tahun' : '-' }}
                        @if($ibuHamil->tanggal_lahir)
                            <span class="text-slate-400 font-normal">({{ $ibuHamil->tanggal_lahir->format('d/m/Y') }})</span>
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Nama Suami</span>
                    <span class="font-semibold text-slate-800">{{ $ibuHamil->nama_suami ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">No Handphone / WA</span>
                    <span class="font-semibold text-slate-800">{{ $ibuHamil->no_hp ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Alamat Tempat Tinggal</span>
                    <span class="text-slate-700 leading-relaxed">{{ $ibuHamil->alamat ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Info Kehamilan & Paritas -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-teal-800 border-b border-slate-100 pb-2">
                Riwayat Obstetri & Taksiran Lahir
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Hari Pertama Haid Terakhir (HPHT)</span>
                    <span class="font-semibold text-slate-800">{{ $ibuHamil->hpht ? $ibuHamil->hpht->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Taksiran Persalinan (HPL)</span>
                    <span class="font-bold text-teal-700 text-sm">
                        {{ $ibuHamil->taksiran_persalinan ? $ibuHamil->taksiran_persalinan->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Perkiraan Usia Kehamilan</span>
                    <span class="font-semibold text-slate-800">
                        {{ $ibuHamil->usia_kehamilan_minggu ? '± ' . $ibuHamil->usia_kehamilan_minggu . ' Minggu' : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Status Paritas (Gravida - Para - Abortus)</span>
                    <div class="mt-1 flex gap-2">
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-800 font-bold">G: {{ $ibuHamil->gravida }}</span>
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-800 font-bold">P: {{ $ibuHamil->para }}</span>
                        <span class="px-2 py-1 rounded bg-slate-100 text-slate-800 font-bold">A: {{ $ibuHamil->abortus }}</span>
                        <span class="px-2 py-1 rounded bg-teal-50 text-teal-800 font-semibold">Anak Hidup: {{ $ibuHamil->jumlah_anak_hidup }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biometrik Fisik -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-teal-800 border-b border-slate-100 pb-2">
                Fisik & Biometrik
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Tinggi Badan</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $ibuHamil->tinggi_badan ? $ibuHamil->tinggi_badan . ' cm' : '-' }}
                    </span>
                    @if($ibuHamil->tinggi_badan && $ibuHamil->tinggi_badan <= 145)
                        <span class="text-[10px] text-rose-600 font-semibold block mt-0.5">⚠️ Terlalu pendek (≤ 145 cm)</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Berat Badan Awal</span>
                    <span class="font-bold text-slate-800 text-sm">
                        {{ $ibuHamil->berat_badan ? $ibuHamil->berat_badan . ' kg' : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Golongan Darah</span>
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-50 text-rose-700 font-bold text-xs mt-1 border border-rose-200">
                        {{ $ibuHamil->golongan_darah ?? '?' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Status Kehamilan</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $ibuHamil->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $ibuHamil->is_active ? 'Kehamilan Aktif' : 'Selesai / Melahirkan' }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Riwayat Skrining Poedji Rochjati -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Riwayat Skrining Skor Poedji Rochjati (SPR)</h3>
                <p class="text-xs text-slate-500">Evaluasi perkembangan risiko tinggi selama masa kehamilan</p>
            </div>
            <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                + Tambah Skrining Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal Skrining</th>
                        <th class="py-3 px-4">Usia Kehamilan / Trimester</th>
                        <th class="py-3 px-4 text-center">Total Skor</th>
                        <th class="py-3 px-4 text-center">Kategori Risiko</th>
                        <th class="py-3 px-4">Petugas Pemeriksa</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ibuHamil->skrinings as $s)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $s->tanggal_skrining->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $s->usia_kehamilan_minggu ? $s->usia_kehamilan_minggu . ' Minggu' : '-' }}
                                @if($s->trimester)
                                    <span class="text-slate-400">(Trimester {{ $s->trimester }})</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="text-sm font-extrabold text-slate-900">{{ $s->total_skor }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $s->kategori_risiko->badgeClasses() }}">
                                    {{ $s->kategori_risiko->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $s->user?->name }} 
                                <span class="text-[10px] text-slate-400">({{ $s->user?->role->label() }})</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('skrining.show', $s) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                        Jawaban
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('skrining.print', $s) }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                        Cetak Kartu
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Ibu hamil ini belum pernah diskrining Skor Poedji Rochjati.
                                <div class="mt-2">
                                    <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" class="text-xs font-semibold text-teal-700 hover:underline">
                                        Lakukan Skrining Sekarang →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Kunjungan ANC -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Riwayat Kunjungan ANC (Pemeriksaan Kehamilan)</h3>
                <p class="text-xs text-slate-500">Pencatatan kehadiran (K1 s/d K6) dan status pelayanan medis</p>
            </div>
            @if(!auth()->user()->isKader())
                <a href="{{ route('kunjungan.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                    + Catat Kunjungan Baru
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal Kunjungan</th>
                        <th class="py-3 px-4">Jenis Kunjungan</th>
                        <th class="py-3 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3 px-4 text-center">Status Dilayani</th>
                        <th class="py-3 px-4">Catatan Pemeriksaan</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ibuHamil->kunjungans as $k)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $k->tanggal_kunjungan->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-bold font-mono">
                                    {{ $k->jenis_kunjungan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_hadir)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        ✓ Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                        ✕ Tidak Hadir
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_dilayani)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        ✓ Dilayani
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                        Belum Dilayani
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $k->catatan ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $k->user?->name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan kunjungan ANC untuk ibu hamil ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

