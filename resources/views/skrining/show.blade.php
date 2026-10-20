@extends('layouts.app', ['title' => 'Rincian Skrining: ' . $skrining->ibuHamil->nama, 'header' => 'Rincian Skrining Deteksi Dini'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <a href="{{ route('ibu-hamil.show', $skrining->ibuHamil) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-1">
                ← Kembali ke Profil {{ $skrining->ibuHamil->nama }}
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">{{ $skrining->ibuHamil->nama }}</h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $skrining->kategori_risiko->badgeClasses() }}">
                    Skor Total: {{ $skrining->total_skor }} • {{ $skrining->kategori_risiko->label() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Pemeriksaan tanggal {{ $skrining->tanggal_skrining->translatedFormat('d F Y') }} • Oleh {{ $skrining->user?->name }} ({{ $skrining->user?->role->label() }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($skrining->kategori_risiko, [\App\Enums\KategoriRisiko::KRST, \App\Enums\KategoriRisiko::KRT]))
                @php
                    $bumil = $skrining->ibuHamil;
                    $waRujukanMsg = urlencode("Halo Dokter / Bidan Puskesmas,\nLaporan Tele-Rujukan Hasil Deteksi Dini SPR:\n- Nama Ibu: {$bumil->nama}\n- NIK: " . ($bumil->nik ?? '-') . "\n- Usia: " . ($bumil->usia ?? '-') . " Th | Gravida ke-" . ($bumil->gravida ?? 1) . "\n- Tanggal Skrining: " . $skrining->tanggal_skrining->translatedFormat('d F Y') . "\n- Klasifikasi Risiko: " . $skrining->kategori_risiko->label() . " (Skor Total: {$skrining->total_skor})\n- Tensi: " . ($bumil->tekanan_darah_sistol ? $bumil->tekanan_darah_sistol . '/' . $bumil->tekanan_darah_diastol . ' mmHg' : '-') . "\n- Hb: " . ($bumil->hemoglobin ? $bumil->hemoglobin . ' g/dL' : '-') . " | LiLA: " . ($bumil->lila ? $bumil->lila . ' cm' : '-') . "\n- Desa / Faskes: " . ($bumil->desa?->nama ?? '-') . " (" . ($bumil->desa?->puskesmas ?? 'Puskesmas') . ")\n- Pemeriksa: " . ($skrining->user?->name ?? 'Tenaga Kesehatan') . "\nMohon arahan dan konfirmasi rujukan faskes. Terima kasih.");
                @endphp
                <a href="https://api.whatsapp.com/send?text={{ $waRujukanMsg }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition-colors">
                    <span>💬 Kirim Tele-Rujukan WA</span>
                </a>
            @endif
            <a href="{{ route('skrining.print', $skrining) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Kartu Skor</span>
            </a>
        </div>
    </div>

    <!-- Rekomendasi Rujukan Banner -->
    <div class="p-5 rounded-3xl border shadow-xs 
        {{ $skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST ? 'bg-rose-50 border-rose-300 text-rose-900' : ($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900') }}">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl shrink-0
                {{ $skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST ? 'bg-rose-600 text-white animate-pulse' : ($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white') }}">
                {{ $skrining->kategori_risiko->value }}
            </div>
            <div class="flex-1">
                <h3 class="text-base font-extrabold">
                    Klasifikasi: {{ $skrining->kategori_risiko->label() }} (Total Skor: {{ $skrining->total_skor }})
                </h3>
                <div class="mt-2 text-xs space-y-1">
                    @if($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST)
                        <p class="font-bold">⚠️ Rujukan Segera ke Rumah Sakit (RS) Rujukan Tingkat Lanjut.</p>
                        <p>Penolong persalinan harus Dokter Spesialis Obstetri & Ginekologi (SpOG). Pasien memerlukan persiapan transfusi darah dan fasilitas bedah sesar.</p>
                    @elseif($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT)
                        <p class="font-bold">⚡ Rujukan Terencana / Konsultasi Dokter Puskesmas PONED atau RS.</p>
                        <p>Penolong persalinan adalah Bidan didampingi Dokter di fasilitas kesehatan yang memadai (Puskesmas mampu PONED / RSUD).</p>
                    @else
                        <p class="font-bold">✓ Kehamilan Risiko Rendah (Normal).</p>
                        <p>Dapat bersalin normal di Polindes, Poskesdes, Praktik Mandiri Bidan (BPM), atau Puskesmas dengan penolong Bidan.</p>
                    @endif
                </div>

                @if($skrining->catatan)
                    <div class="mt-3 pt-3 border-t border-slate-200/40 text-xs italic">
                        Catatan Pemeriksa: "{{ $skrining->catatan }}"
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Rincian Jawaban 23 Pertanyaan -->
    <div class="space-y-4">
        <h3 class="text-base font-bold text-slate-800 px-1">Rincian Jawaban Kuesioner Deteksi Dini (23 Pertanyaan)</h3>

        <!-- Kelompok I -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center justify-between">
                <span>Kelompok I: Ada Potensi Gawat Obstetri (APGO)</span>
                <span class="text-slate-500 font-normal">13 Pertanyaan</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($kelompokI as $detail)
                    <div class="p-4 flex items-center justify-between gap-4 {{ $detail->jawaban ? 'bg-amber-50/40 font-semibold' : '' }}">
                        <div class="text-xs text-slate-800">
                            <span class="font-mono text-slate-400 mr-1.5">#{{ $detail->pertanyaanSpr->nomor }}</span>
                            {{ $detail->pertanyaanSpr->pertanyaan }}
                        </div>
                        <div class="shrink-0">
                            @if($detail->jawaban)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    ✓ YA (+{{ $detail->skor }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-slate-400 bg-slate-50">
                                    ✕ Tidak (0)
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kelompok II -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-xs text-slate-700 uppercase tracking-wider flex items-center justify-between">
                <span>Kelompok II: Ada Gawat Obstetri (AGO)</span>
                <span class="text-slate-500 font-normal">8 Pertanyaan</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($kelompokII as $detail)
                    <div class="p-4 flex items-center justify-between gap-4 {{ $detail->jawaban ? 'bg-amber-50/40 font-semibold' : '' }}">
                        <div class="text-xs text-slate-800">
                            <span class="font-mono text-slate-400 mr-1.5">#{{ $detail->pertanyaanSpr->nomor }}</span>
                            {{ $detail->pertanyaanSpr->pertanyaan }}
                        </div>
                        <div class="shrink-0">
                            @if($detail->jawaban)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $detail->skor >= 8 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    ✓ YA (+{{ $detail->skor }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-slate-400 bg-slate-50">
                                    ✕ Tidak (0)
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Kelompok III -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-xs text-rose-800 uppercase tracking-wider flex items-center justify-between">
                <span>Kelompok III: Ada Gawat Darurat Obstetri (AGDO)</span>
                <span class="text-slate-500 font-normal">2 Pertanyaan (@ Skor 8)</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($kelompokIII as $detail)
                    <div class="p-4 flex items-center justify-between gap-4 {{ $detail->jawaban ? 'bg-rose-50/60 font-semibold' : '' }}">
                        <div class="text-xs text-slate-800">
                            <span class="font-mono text-slate-400 mr-1.5">#{{ $detail->pertanyaanSpr->nomor }}</span>
                            {{ $detail->pertanyaanSpr->pertanyaan }}
                        </div>
                        <div class="shrink-0">
                            @if($detail->jawaban)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 animate-pulse">
                                    ⚠️ YA (+{{ $detail->skor }})
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs text-slate-400 bg-slate-50">
                                    ✕ Tidak (0)
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection

