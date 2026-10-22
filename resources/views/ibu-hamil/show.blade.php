@extends('layouts.app', ['title' => 'Rincian Ibu Hamil: ' . $ibuHamil->nama, 'header' => 'Rincian Pasien Ibu Hamil'])

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    activeKunjunganId: null,
    activeJenis: '',
    activeTanggal: '',
    activeCatatan: '',
    activeHadir: true,
    activeDilayani: true,
    hasSkrining: false,
    activeTb: '',
    activeBb: '',
    activeSistol: '',
    activeDiastol: '',
    activeLila: '',
    activeTfu: '',
    activeDjj: '',
    activeHb: '',
    activeKepatuhanFe: true,
    activeJadwalBerikutnya: '',
    openConfirmModal(id, jenis, tanggal, catatan = '', hadir = true, dilayani = true, tb = '', bb = '', sistol = '', diastol = '', lila = '', tfu = '', djj = '', hb = '', kepatuhanFe = true, jadwalNext = '') {
        this.activeKunjunganId = id;
        this.activeJenis = jenis;
        this.activeTanggal = tanggal;
        this.activeCatatan = catatan;
        this.activeHadir = hadir;
        this.activeDilayani = dilayani;
        this.hasSkrining = dilayani;
        this.activeTb = tb;
        this.activeBb = bb;
        this.activeSistol = sistol;
        this.activeDiastol = diastol;
        this.activeLila = lila;
        this.activeTfu = tfu;
        this.activeDjj = djj;
        this.activeHb = hb;
        this.activeKepatuhanFe = kepatuhanFe;
        this.activeJadwalBerikutnya = jadwalNext;
        this.modalOpen = true;

        this.$nextTick(() => {
            if (typeof initDatepickers === 'function') {
                initDatepickers();
            }
            const tEl = document.querySelector('input[name=tanggal_kunjungan]');
            if (tEl && tEl._flatpickr && tanggal) {
                tEl._flatpickr.setDate(tanggal, true);
            }
            const jEl = document.querySelector('input[name=jadwal_kunjungan_berikutnya]');
            if (jEl && jEl._flatpickr) {
                jEl._flatpickr.setDate(jadwalNext || '', true);
            }
        });
    }
}">

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
            @php
                $latestK = $ibuHamil->kunjungans->sortByDesc('tanggal_kunjungan')->first();
            @endphp
            <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $ibuHamil->id }}{{ $latestK ? '&kunjungan_id=' . $latestK->id . '&tanggal=' . $latestK->tanggal_kunjungan->format('Y-m-d') : '' }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Skrining Risiko Baru</span>
            </a>

            <a href="{{ route('kunjungan.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Catat Kunjungan / Jemput Bola</span>
            </a>

            <a href="{{ route('ibu-hamil.edit', $ibuHamil) }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <!-- Alert Box for Cross-District / Emergency Point Care -->
    @if(auth()->user()->desa_id && auth()->user()->desa_id !== $ibuHamil->desa_id)
        <div class="p-4 rounded-3xl bg-sky-50 border border-sky-300 text-sky-950 flex items-start gap-3.5 shadow-xs">
            <div class="w-10 h-10 rounded-2xl bg-sky-600 text-white flex items-center justify-center shrink-0 text-xl font-bold shadow-xs">
                🌐
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h4 class="font-bold text-sm text-sky-950">Pelayanan Ibu Hamil Lintas Daerah / Titik Kritis</h4>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-sky-200/80 text-sky-900 border border-sky-300">
                        Pasien Luar Wilayah
                    </span>
                </div>
                <p class="text-xs text-sky-800 mt-1 leading-relaxed">
                    Ibu Hamil ini terdaftar di Desa <strong>{{ $ibuHamil->desa?->nama }}</strong> ({{ $ibuHamil->desa?->puskesmas ?? 'Puskesmas Asal' }}). 
                    Sebagai tenaga kesehatan di wilayah <strong>{{ auth()->user()->desa?->nama ?? 'wilayah Anda' }}</strong>, Anda memiliki wewenang untuk memeriksa ANC, mendampingi jemput bola, serta mengisi skrining SPR di titik kritis demi keselamatan ibu dan janin.
                </p>
            </div>
        </div>
    @endif

    <!-- Alert Box for High Risk KRST / KRT with Tele-Consultation -->
    @if($latestSkrining && in_array($latestSkrining->kategori_risiko, [\App\Enums\KategoriRisiko::KRST, \App\Enums\KategoriRisiko::KRT]))
        @php
            $isKrst = $latestSkrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST;
            $waKonsulText = urlencode("Halo Bidan / Dokter Puskesmas,\nMohon konsultasi & tele-rujukan untuk Ibu Hamil terdeteksi risiko tinggi:\n- Nama: {$ibuHamil->nama}\n- NIK: " . ($ibuHamil->nik ?? '-') . "\n- Usia: " . ($ibuHamil->usia ?? '-') . " Th | Gravida ke-" . ($ibuHamil->gravida ?? 1) . "\n- Kategori Risiko: " . $latestSkrining->kategori_risiko->label() . " (Skor SPR: {$latestSkrining->total_skor})\n- Tensi: " . ($ibuHamil->tekanan_darah_sistol ? $ibuHamil->tekanan_darah_sistol . '/' . $ibuHamil->tekanan_darah_diastol . ' mmHg' : '-') . "\n- Hb: " . ($ibuHamil->hemoglobin ? $ibuHamil->hemoglobin . ' g/dL' : '-') . " | LiLA: " . ($ibuHamil->lila ? $ibuHamil->lila . ' cm' : '-') . "\n- Wilayah: " . ($ibuHamil->desa?->nama ?? '-') . " (" . ($ibuHamil->desa?->puskesmas ?? 'Puskesmas') . ")\n- Faskes Pendamping: " . (auth()->user()->desa?->nama ?? auth()->user()->name) . "\nMohon arahan dan verifikasi penanganan medis lebih lanjut. Terima kasih.");
        @endphp
        <div class="p-4 rounded-3xl {{ $isKrst ? 'bg-rose-50 border border-rose-300 text-rose-900 animate-pulse-subtle' : 'bg-amber-50 border border-amber-300 text-amber-900' }} flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 {{ $isKrst ? 'text-rose-600' : 'text-amber-600' }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <h4 class="font-bold text-sm {{ $isKrst ? 'text-rose-900' : 'text-amber-900' }}">
                        PERINGATAN {{ $latestSkrining->kategori_risiko->label() }} (Skor SPR: {{ $latestSkrining->total_skor }})
                    </h4>
                    <p class="text-xs {{ $isKrst ? 'text-rose-700' : 'text-amber-700' }} mt-0.5">
                        {{ $isKrst ? 'Pasien memiliki faktor risiko gawat darurat atau komplikasi obstetri tinggi. Rekomendasi: Segera lakukan rujukan ke Rumah Sakit dan penanganan oleh SpOG.' : 'Pasien memiliki faktor risiko tinggi kehamilan. Rekomendasi: Konsultasi tele-health dokter puskesmas dan faskes memadai.' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
                <a href="https://api.whatsapp.com/send?text={{ $waKonsulText }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs whitespace-nowrap transition-colors">
                    <span>💬 Tele-Konsultasi Dokter / Bidan (WA)</span>
                </a>
                <a href="{{ route('skrining.print', $latestSkrining) }}" target="_blank"
                   class="inline-flex items-center gap-1 px-3 py-2 rounded-xl {{ $isKrst ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-600 hover:bg-amber-700' }} text-white text-xs font-bold shadow-xs whitespace-nowrap transition-colors">
                    <span>🖨️ Rujukan</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Profile & Obstetric Cards Grid -->
    @php
        $rawBumilPhone = preg_replace('/[^0-9]/', '', $ibuHamil->no_hp ?? '');
        $waBumilPhone = $rawBumilPhone && str_starts_with($rawBumilPhone, '0') ? '62' . substr($rawBumilPhone, 1) : $rawBumilPhone;
        $latestK = $ibuHamil->kunjungans->sortByDesc('tanggal_kunjungan')->first();
        $tglNext = $latestK?->jadwal_kunjungan_berikutnya ? $latestK->jadwal_kunjungan_berikutnya->translatedFormat('d F Y') : ($latestK?->tanggal_kunjungan ? $latestK->tanggal_kunjungan->translatedFormat('d F Y') : '-');

        // Pesan 1: Pengingat Jadwal ANC / Jemput Bola
        $msgJadwal = urlencode("Halo Ibu {$ibuHamil->nama},\n\nSalam sehat dari Tim Pendampingan Telehealth ANC Puskesmas " . ($ibuHamil->desa?->puskesmas ?? 'Puskesmas') . " / Desa " . ($ibuHamil->desa?->nama ?? '') . ".\n\nKami mengingatkan jadwal pemeriksaan ANC / Kunjungan Jemput Bola berikutnya pada hari *{$tglNext}*.\n\n📌 *Pesan Pemantauan Kesehatan:*\n1. Pastikan rutin meminum tablet tambah darah (Fe) 1 tablet setiap malam.\n2. Tetap cukupi asupan cairan dan makanan bergizi seimbang.\n3. Waspadai tanda bahaya: pusing hebat berkunang, pandangan kabur, atau bengkak kaki.\n\nJika ada keluhan segera hubungi bidan atau kader pendamping. Semoga ibu dan kandungan selalu sehat!");

        // Pesan 2: Edukasi Kepatuhan Tablet Fe & Nutrisi
        $msgFe = urlencode("Halo Ibu {$ibuHamil->nama},\n\nEdukasi Kesehatan Telehealth Puskesmas " . ($ibuHamil->desa?->puskesmas ?? 'Puskesmas') . ":\n\n💊 *Pentingnya Tablet Tambah Darah (Fe):*\n1. Minum 1 tablet Fe setiap hari (disarankan malam hari sebelum tidur).\n2. Minum dengan air putih atau jus buah (hindari teh/kopi/susu berdekatan dengan konsumsi obat).\n3. Konsumsi makanan kaya zat besi seperti bayam, hati ayam, telur, dan ikan.\n\nKepatuhan minum tablet Fe mencegah anemia dan menjaga berat lahir bayi tetap optimal!");

        // Pesan 3: Edukasi Waspada Tanda Bahaya Kehamilan
        $msgBahaya = urlencode("Halo Ibu {$ibuHamil->nama} dan Bapak " . ($ibuHamil->nama_suami ?? 'Keluarga') . ",\n\n⚠️ *Peringatan Telehealth: Kenali 6 Tanda Bahaya Kehamilan:*\n1. Perdarahan jalan lahir (flek atau darah segar).\n2. Pusing hebat yang tidak hilang dan mata berkunang-kunang.\n3. Bengkak pada muka, tangan, dan kaki.\n4. Gerakan janin berkurang atau tidak terasa.\n5. Demam tinggi atau kejang.\n6. Keluar air ketuban sebelum waktunya.\n\nJika mengalami salah satu tanda di atas, SEGERA hubungi Bidan Desa atau bawa ke Puskesmas terdekat!");
    @endphp

    <!-- Pusat Telehealth Langsung ke Ibu Hamil & Keluarga -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-3xl p-5 text-white shadow-md relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white backdrop-blur">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        Modul Telehealth & Pendampingan Digital
                    </span>
                    <span class="text-xs text-teal-100">WhatsApp Langsung ke Pasien</span>
                </div>
                <h3 class="text-lg font-extrabold tracking-tight">Kirim Layanan Telehealth ke Ibu Hamil</h3>
                <p class="text-xs text-teal-100 mt-0.5">
                    Nomor WhatsApp Terdata: <strong class="text-white font-mono">{{ $ibuHamil->no_hp ?? 'Belum ada nomor HP' }}</strong> • Suami: {{ $ibuHamil->nama_suami ?? '-' }}
                </p>
            </div>

            @if($waBumilPhone)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="https://api.whatsapp.com/send?phone={{ $waBumilPhone }}&text={{ $msgJadwal }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-900 text-xs font-bold shadow-xs active:scale-95 transition-all">
                        <span>🗓️ Pengingat Jadwal ANC</span>
                    </a>
                    <a href="https://api.whatsapp.com/send?phone={{ $waBumilPhone }}&text={{ $msgFe }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-800/80 hover:bg-emerald-800 text-white text-xs font-semibold backdrop-blur active:scale-95 transition-all">
                        <span>💊 Edukasi Tablet Fe</span>
                    </a>
                    <a href="https://api.whatsapp.com/send?phone={{ $waBumilPhone }}&text={{ $msgBahaya }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-700/80 hover:bg-rose-700 text-white text-xs font-semibold backdrop-blur active:scale-95 transition-all">
                        <span>⚠️ Tanda Bahaya</span>
                    </a>
                </div>
            @else
                <div class="text-xs text-amber-200 bg-amber-900/40 px-3 py-2 rounded-xl border border-amber-400/30">
                    ⚠️ Nomor WhatsApp belum dimasukkan. Silakan klik tombol <strong>Edit</strong> untuk menambahkan nomor HP.
                </div>
            @endif
        </div>
    </div>

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

    <!-- Hasil Pemeriksaan Fisik ANC & Lokasi GPS -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Hasil Pemeriksaan Fisik Ibu Hamil (Standar ANC)</h3>
                <p class="text-xs text-slate-500">Tanda-tanda vital, status gizi ibu, kondisi janin, dan koordinat tempat tinggal</p>
            </div>
            @if($ibuHamil->is_kek || $ibuHamil->is_anemia)
                <div class="flex items-center gap-2">
                    @if($ibuHamil->is_kek)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                            ⚠️ Indikasi KEK (LiLA &lt; 23.5 cm)
                        </span>
                    @endif
                    @if($ibuHamil->is_anemia)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                            ⚠️ Anemia (Hb &lt; 11 g/dL)
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 text-xs">
            <!-- Tekanan Darah -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Tekanan Darah</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->tekanan_darah ?? '-' }}
                </span>
            </div>

            <!-- LiLA -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Lingkar Lengan (LiLA)</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->lila ? $ibuHamil->lila . ' cm' : '-' }}
                </span>
                @if($ibuHamil->is_kek)
                    <span class="text-[10px] text-rose-600 font-semibold block mt-0.5">Risiko KEK</span>
                @endif
            </div>

            <!-- Hemoglobin -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Hemoglobin (Hb)</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->hemoglobin ? $ibuHamil->hemoglobin . ' g/dL' : '-' }}
                </span>
                @if($ibuHamil->is_anemia)
                    <span class="text-[10px] text-amber-600 font-semibold block mt-0.5">Anemia</span>
                @endif
            </div>

            <!-- TFU -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Tinggi Fundus (TFU)</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->tfu ? $ibuHamil->tfu . ' cm' : '-' }}
                </span>
            </div>

            <!-- DJJ -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Denyut Janin (DJJ)</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->djj ? $ibuHamil->djj . ' bpm' : '-' }}
                </span>
            </div>

            <!-- Status Imunisasi TT -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Imunisasi TT</span>
                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                    {{ $ibuHamil->status_imunisasi_tt ?? '-' }}
                </span>
            </div>

            <!-- Status Gizi -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Status Gizi</span>
                <span class="font-semibold text-slate-800 mt-0.5 block uppercase">
                    {{ $ibuHamil->status_gizi ?? '-' }}
                </span>
            </div>

            <!-- Refleks Patela -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Refleks Patela</span>
                <span class="font-semibold text-slate-800 mt-0.5 block capitalize">
                    {{ $ibuHamil->refleks_patela ? $ibuHamil->refleks_patela . ' (+)' : '-' }}
                </span>
            </div>

            <!-- Edema -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Edema / Bengkak</span>
                <span class="font-semibold text-slate-800 mt-0.5 block capitalize">
                    {{ $ibuHamil->edema ? str_replace('_', ' ', $ibuHamil->edema) : '-' }}
                </span>
            </div>

            <!-- Kondisi Umum -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                <span class="text-slate-400 block text-[11px] font-medium">Kondisi Umum</span>
                <span class="font-semibold text-slate-800 mt-0.5 block capitalize">
                    {{ $ibuHamil->kondisi_umum ?? '-' }}
                </span>
            </div>

            <!-- Koordinat GPS -->
            <div class="sm:col-span-2 p-3.5 rounded-2xl bg-teal-50/50 border border-teal-200">
                <span class="text-teal-700 block text-[11px] font-bold">📍 Koordinat Lokasi GPS</span>
                @if($ibuHamil->latitude && $ibuHamil->longitude)
                    <div class="flex items-center justify-between mt-1">
                        <span class="font-mono text-slate-800 text-xs font-semibold">
                            {{ $ibuHamil->latitude }}, {{ $ibuHamil->longitude }}
                        </span>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $ibuHamil->latitude }},{{ $ibuHamil->longitude }}"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-700 hover:text-teal-900 bg-white px-2.5 py-1 rounded-lg border border-teal-300 shadow-2xs">
                            🗺️ Buka Peta
                        </a>
                    </div>
                @else
                    <span class="text-slate-400 italic mt-0.5 block">Belum ada koordinat GPS tersimpan.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Riwayat Skrining Risiko Kehamilan -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Riwayat Skrining Deteksi Dini Risiko</h3>
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
                                <div class="font-medium text-slate-800">{{ $s->user?->name }} <span class="text-[10px] text-slate-400">({{ $s->user?->role->label() }})</span></div>
                                @if ($s->user?->desa_id && $s->user?->desa_id !== $ibuHamil->desa_id)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200 mt-0.5" title="Petugas dari {{ $s->user?->desa?->nama }}">
                                        🌐 {{ $s->user?->desa?->nama }}
                                    </span>
                                @endif
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
                                Ibu hamil ini belum pernah diskrining deteksi dini risiko kehamilan.
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

    <!-- Riwayat Kunjungan ANC & Jemput Bola -->
    @php
        $ibuHasSkrining = $ibuHamil->skrinings()->exists();
    @endphp
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden"
         x-data="{
             modalOpen: false,
             activeKunjunganId: null,
             activeJenis: '',
             activeHadir: false,
             activeDilayani: false,
             activeTanggal: '',
             activeCatatan: '',
             activeTb: '',
             activeBb: '',
             activeSistol: '',
             activeDiastol: '',
             activeLila: '',
             activeTfu: '',
             activeDjj: '',
             activeHb: '',
             activeKepatuhanFe: true,
             activeJadwalBerikutnya: '',
             hasSkrining: false,
             openConfirmModal(id, jenis, tgl, catatan, hadir, dilayani, tb = '', bb = '', sistol = '', diastol = '', lila = '', tfu = '', djj = '', hb = '', kepatuhanFe = true, jadwalBerikutnya = '', hasSkrining = false) {
                 this.activeKunjunganId = id;
                 this.activeJenis = jenis;
                 this.activeTanggal = tgl;
                 this.activeCatatan = catatan || '';
                 this.activeHadir = Boolean(hadir);
                 this.activeDilayani = Boolean(dilayani);
                 this.activeTb = tb || '';
                 this.activeBb = bb || '';
                 this.activeSistol = sistol || '';
                 this.activeDiastol = diastol || '';
                 this.activeLila = lila || '';
                 this.activeTfu = tfu || '';
                 this.activeDjj = djj || '';
                 this.activeHb = hb || '';
                 this.activeKepatuhanFe = Boolean(kepatuhanFe);
                 this.activeJadwalBerikutnya = jadwalBerikutnya || '';
                 this.hasSkrining = Boolean(hasSkrining);
                 this.modalOpen = true;
             }
         }">
        <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                        Jemput Bola & ANC
                    </span>
                    <span class="text-xs text-slate-500">Pendampingan Ibu Hamil</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Riwayat Kunjungan & Pendampingan ANC</h3>
                <p class="text-xs text-slate-500">Pencatatan kehadiran (K1 s/d K6), metode kunjungan rumah/posyandu, dan pemeriksaan fisik 10T</p>
            </div>
            <div class="flex items-center gap-2">
                @if($ibuHamil->kunjungans->count() < 6)
                    <form action="{{ route('kunjungan.generate', $ibuHamil) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-200 transition-all shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Lengkapi Jadwal K1-K6 Otomatis</span>
                        </button>
                    </form>
                @endif
                <a href="{{ route('kunjungan.create') }}?ibu_hamil_id={{ $ibuHamil->id }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Kunjungan Baru</span>
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal & Metode</th>
                        <th class="py-3 px-4 text-center">Tahap ANC</th>
                        <th class="py-3 px-4">Pemeriksaan Fisik</th>
                        <th class="py-3 px-4">Pendampingan / Dokumentasi</th>
                        <th class="py-3 px-4 text-center">Jadwal Berikutnya</th>
                        <th class="py-3 px-4 text-center">Status Kehadiran</th>
                        <th class="py-3 px-4 text-center">Status Dilayani</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4 text-right">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ibuHamil->kunjungans as $k)
                        @php
                            $hasPhysicalData = ! empty($k->berat_badan) && ! empty($k->tekanan_darah_sistol);
                            $hasSkriningK = $k->skrining_id !== null;
                            $tbK = $k->tinggi_badan;
                            $bbK = $k->berat_badan;
                            $sistolK = $k->tekanan_darah_sistol;
                            $diastolK = $k->tekanan_darah_diastol;
                            $lilaK = $k->lila;
                            $tfuK = $k->tfu;
                            $djjK = $k->djj;
                            $hbK = $k->hb;
                            $kepatuhanFeK = $k->kepatuhan_fe ? 1 : 0;
                            $jadwalNextK = $k->jadwal_kunjungan_berikutnya ? $k->jadwal_kunjungan_berikutnya->format('Y-m-d') : '';

                            $modalTb = $tbK ?? '';
                            $modalBb = $bbK ?? '';
                            $modalSistol = $sistolK ?? '';
                            $modalDiastol = $diastolK ?? '';
                            $modalLila = $lilaK ?? '';
                            $modalHb = $hbK ?? '';
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <div>{{ $k->tanggal_kunjungan->translatedFormat('d F Y') }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    @if ($k->metode_kunjungan === 'jemput_bola')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            🏠 Jemput Bola
                                        </span>
                                    @elseif ($k->metode_kunjungan === 'puskesmas')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                            🏥 Puskesmas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800">
                                            👥 Posyandu
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 font-bold font-mono text-xs border border-teal-200/60">
                                    {{ $k->tahap_anc ?? $k->jenis_kunjungan }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                @if ($hasPhysicalData)
                                    <div class="flex flex-col gap-0.5 text-[11px]">
                                        <span><strong class="text-slate-800">TB/BB:</strong> {{ $tbK ? $tbK . ' cm' : '-' }} / {{ $bbK ? $bbK . ' kg' : '-' }}</span>
                                        <span><strong class="text-slate-800">TD:</strong> {{ ($sistolK && $diastolK) ? "{$sistolK}/{$diastolK} mmHg" : '-' }}</span>
                                        <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                            @if ($lilaK)
                                                <span>LiLA: <strong class="text-slate-700">{{ $lilaK }} cm</strong></span>
                                            @endif
                                            @if ($hbK)
                                                <span>Hb: <strong class="text-slate-700">{{ $hbK }} g/dL</strong></span>
                                            @endif
                                            @if ($k->kepatuhan_fe)
                                                <span class="text-emerald-700 font-bold" title="Patuh Tablet Fe">✓ Fe</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        ⚠️ Belum Diukur
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                <div class="text-[11px] truncate" title="{{ $k->bentuk_pendampingan ?? $k->catatan }}">
                                    {{ $k->bentuk_pendampingan ?? ($k->catatan ?? '-') }}
                                </div>
                                @if ($k->foto_pendampingan)
                                    <a href="{{ $k->foto_pendampingan_url }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[10px] text-teal-700 hover:text-teal-800 font-semibold mt-1">
                                        📷 Lihat Foto Pendampingan
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($k->jadwal_kunjungan_berikutnya)
                                    <div class="inline-flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            🗓️ {{ $k->jadwal_kunjungan_berikutnya->translatedFormat('d M Y') }}
                                        </span>
                                        @if(!empty($ibuHamil->no_hp))
                                            @php
                                                $waBumilPhone = preg_replace('/[^0-9]/', '', $ibuHamil->no_hp);
                                                if (str_starts_with($waBumilPhone, '0')) {
                                                    $waBumilPhone = '62' . substr($waBumilPhone, 1);
                                                }
                                                $waReminderText = urlencode("Halo Ibu {$ibuHamil->nama},\n\nKami dari Tim Pendamping Kader Posyandu & Faskes " . ($k->desa?->nama ?? 'wilayah kerja') . ".\nMengingatkan jadwal pemeriksaan ANC jemput bola / posyandu berikutnya pada tanggal: " . $k->jadwal_kunjungan_berikutnya->translatedFormat('d F Y') . ".\n\nMohon siapkan buku KIA serta jaga kesehatan dan istirahat yang cukup. Sehat selalu untuk Ibu dan calon buah hati!\n\nSalam sehat,\n" . auth()->user()->name);
                                            @endphp
                                            <a href="https://api.whatsapp.com/send?phone={{ $waBumilPhone }}&text={{ $waReminderText }}" target="_blank"
                                               title="Kirim Pengingat Jadwal via WhatsApp Tele-Reminder"
                                               class="px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold inline-flex items-center gap-1 transition-colors">
                                                <span>📲 Kirim WA</span>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[10px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_hadir)
                                    @if (!$hasPhysicalData)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                            ⚠️ Hadir (Data Belum Diisi)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                            ✓ Hadir & Diperiksa
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        ⏳ Belum Hadir
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k->status_dilayani && $hasSkriningK)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Dilayani Lengkap
                                    </span>
                                    @if ($k->skrining)
                                        <div class="mt-1">
                                            <a href="{{ route('skrining.show', $k->skrining) }}"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $k->skrining->kategori_risiko->badgeClasses() }}">
                                                {{ $k->skrining->kategori_risiko->value }} ({{ $k->skrining->total_skor }})
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        Belum Skrining SPR
                                    </span>
                                    <a href="{{ route('skrining.create', ['ibu_hamil_id' => $ibuHamil->id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}"
                                       class="block text-[10px] text-teal-700 font-bold hover:underline mt-1">
                                        + Isi Skrining SPR
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div class="font-medium text-slate-800">{{ $k->user?->name ?? '-' }}</div>
                                @if ($k->desa_id && $k->desa_id !== $ibuHamil->desa_id)
                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200 mt-0.5" title="Pelayanan di {{ $k->desa?->nama }}">
                                        🌐 {{ $k->desa?->nama }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(!$k->status_hadir)
                                        <button type="button" 
                                                @click="openConfirmModal({{ $k->id }}, '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuK }}', '{{ $djjK }}', '{{ $modalHb }}', {{ $kepatuhanFeK }}, '{{ $jadwalNextK }}', {{ $hasSkriningK ? 'true' : 'false' }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                            <span>✓ Hadirkan & Ukur</span>
                                        </button>
                                    @elseif(!$hasPhysicalData)
                                        <button type="button" 
                                                @click="openConfirmModal({{ $k->id }}, '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, false, '{{ $modalTb }}', '{{ $modalBb }}', '{{ $modalSistol }}', '{{ $modalDiastol }}', '{{ $modalLila }}', '{{ $tfuK }}', '{{ $djjK }}', '{{ $modalHb }}', {{ $kepatuhanFeK }}, '{{ $jadwalNextK }}', {{ $hasSkriningK ? 'true' : 'false' }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-all">
                                            <span>⚠️ Input Data Fisik</span>
                                        </button>
                                    @elseif(!$hasSkriningK)
                                        <a href="{{ route('skrining.create', ['ibu_hamil_id' => $ibuHamil->id, 'kunjungan_id' => $k->id, 'tanggal' => $k->tanggal_kunjungan->format('Y-m-d')]) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                            <span>📋 + Skrining SPR</span>
                                        </a>
                                    @else
                                        <button type="button" 
                                                @click="openConfirmModal({{ $k->id }}, '{{ $k->tahap_anc ?? $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbK }}', '{{ $bbK }}', '{{ $sistolK }}', '{{ $diastolK }}', '{{ $lilaK }}', '{{ $tfuK }}', '{{ $djjK }}', '{{ $hbK }}', {{ $kepatuhanFeK }}, '{{ $jadwalNextK }}', true)"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-all">
                                            <span>Ubah Pelayanan</span>
                                        </button>
                                    @endif

                                    <a href="{{ route('kunjungan.edit', $k) }}"
                                       class="text-blue-600 hover:text-blue-700 font-semibold text-xs ml-1">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Belum ada catatan kunjungan ANC untuk ibu hamil ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Modal Konfirmasi Kehadiran -->
        <div x-show="modalOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" 
                     @click="modalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" 
                     x-transition:enter="ease-out duration-300"
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
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider" x-text="'ANC ' + activeJenis"></span>
                                <button type="button" @click="modalOpen = false" class="text-white/80 hover:text-white text-base">✕</button>
                            </div>
                            <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                            <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong class="text-white font-bold">{{ $ibuHamil->nama }}</strong></p>
                        </div>

                        <div class="p-6 space-y-4 text-xs max-h-[75vh] overflow-y-auto">
                            <div>
                                <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="tanggal_kunjungan" x-model="activeTanggal" required
                                           placeholder="HH-BB-TTTT (contoh: 18-10-2026)"
                                           class="datepicker-dmy w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-medium" />
                                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-[10px] text-teal-600 mt-1 block font-medium">Format: Hari-Bulan-Tahun</span>
                            </div>

                            <!-- Pemeriksaan Fisik: TB, BB, LiLA, TD, Hb (Wajib) & TFU, DJJ (Klinis) -->
                            <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik Kunjungan (10T):</span>
                                    <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                                        * Wajib Lengkap (TB, BB, LiLA, TD, Hb)
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TB (cm) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="tinggi_badan" x-model="activeTb" placeholder="158" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            BB (kg) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="berat_badan" x-model="activeBb" placeholder="56" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            LiLA (cm) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="lila" x-model="activeLila" placeholder="24" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TD Sistol <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="tekanan_darah_sistol" x-model="activeSistol" placeholder="120" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            TD Diastol <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" name="tekanan_darah_diastol" x-model="activeDiastol" placeholder="80" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">
                                            Hb (g/dL) <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="number" step="0.1" name="hb" x-model="activeHb" placeholder="11.5" required
                                               class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <template x-if="!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb">
                                        <p class="text-[11px] text-amber-800 font-medium flex items-center gap-1.5 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-200">
                                            <span>⚠️ TB, BB, LiLA, TD Sistol, TD Diastol, dan Hb wajib diisi sebelum menyimpan konfirmasi.</span>
                                        </p>
                                    </template>
                                    <template x-if="activeTb && activeBb && activeSistol && activeDiastol && activeLila && activeHb">
                                        <p class="text-[11px] text-emerald-800 font-semibold flex items-center gap-1.5 bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200">
                                            <span>✓ Data fisik lengkap (TB, BB, LiLA, TD, Hb). Tombol Simpan aktif & siap diarahkan ke Skrining SPR.</span>
                                        </p>
                                    </template>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-teal-200/60">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TFU (cm)</label>
                                        <input type="number" step="0.1" name="tfu" x-model="activeTfu" placeholder="26"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">DJJ (dpm)</label>
                                        <input type="number" name="djj" x-model="activeDjj" placeholder="140"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>

                                <div class="pt-1.5">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="hidden" name="kepatuhan_fe" value="0" />
                                        <input type="checkbox" name="kepatuhan_fe" value="1" x-model="activeKepatuhanFe"
                                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                                        <span class="text-xs font-semibold text-slate-800">
                                            Rutin Mengonsumsi Tablet Fe
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Jadwal Kunjungan Berikutnya -->
                            <div>
                                <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Jadwal Kunjungan / Jemput Bola Berikutnya
                                </label>
                                <div class="relative">
                                    <input type="text" name="jadwal_kunjungan_berikutnya" x-model="activeJadwalBerikutnya"
                                           placeholder="HH-BB-TTTT (contoh: 18-11-2026)"
                                           class="datepicker-dmy w-full rounded-xl border border-slate-300 pl-10 pr-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 font-medium" />
                                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-500 mt-1 block">Format: Hari-Bulan-Tahun</span>
                            </div>

                            <input type="hidden" name="status_hadir" value="1" />
                            <input type="hidden" name="status_dilayani" value="0" />

                            <div>
                                <label class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Catatan Pemeriksaan / Hasil Pelayanan (Opsional)
                                </label>
                                <textarea name="catatan" x-model="activeCatatan" rows="2"
                                          placeholder="Contoh: Tekanan darah normal, telah diberikan tablet Fe..."
                                          class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="modalOpen = false"
                                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                    :disabled="!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb"
                                    :class="(!activeTb || !activeBb || !activeSistol || !activeDiastol || !activeLila || !activeHb) 
                                        ? 'opacity-40 cursor-not-allowed bg-slate-400 hover:bg-slate-400 pointer-events-none' 
                                        : 'bg-teal-600 hover:bg-teal-700 shadow-sm active:scale-95'"
                                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <span x-text="!hasSkrining ? 'Simpan & Lanjut ke Skrining SPR →' : 'Simpan Pemeriksaan Fisik'"></span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

