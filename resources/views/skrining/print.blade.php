<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Skor Risiko Kehamilan - {{ $skrining->ibuHamil->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { font-size: 11px; }
            .no-print { display: none !important; }
            @page { margin: 1cm; size: A4 portrait; }
        }
    </style>
</head>
<body class="bg-slate-100 py-6 print:bg-white print:py-0 text-slate-800 font-sans">

    <!-- Top Action Bar for Screen View -->
    <div class="max-w-4xl mx-auto mb-4 px-4 flex items-center justify-between no-print">
        <a href="{{ route('skrining.show', $skrining) }}" class="text-xs font-semibold text-teal-700 hover:underline">
            ← Kembali ke Aplikasi
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Cetak Kartu Skor (Print / PDF)</span>
        </button>
    </div>

    <!-- Official Printable Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 border border-slate-300 shadow-lg print:shadow-none print:border-none print:p-0">
        
        <!-- Header Puskesmas / Dinkes -->
        <div class="text-center border-b-2 border-slate-900 pb-3 mb-4">
            <h1 class="text-lg font-extrabold uppercase tracking-wide">KARTU SKOR DETEKSI DINI RISIKO KEHAMILAN</h1>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">
                SISTEM DETEKSI DINI RISIKO TINGGI IBU HAMIL (E-DETIMIL)
            </h2>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Wilayah Kerja: {{ $skrining->ibuHamil->desa?->nama }} • {{ $skrining->ibuHamil->desa?->kecamatan }} • {{ $skrining->ibuHamil->desa?->kabupaten }}
            </p>
        </div>

        <!-- Ibu Hamil Bio Grid -->
        <div class="grid grid-cols-2 gap-x-6 gap-y-1.5 text-xs border border-slate-300 p-3 rounded-lg mb-4 bg-slate-50/50">
            <div>
                <span class="text-slate-500 inline-block w-28">Nama Ibu Hamil</span>: 
                <strong class="text-slate-900">{{ $skrining->ibuHamil->nama }}</strong>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">Nama Suami</span>: 
                <span class="text-slate-800">{{ $skrining->ibuHamil->nama_suami ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">NIK</span>: 
                <span class="font-mono">{{ $skrining->ibuHamil->nik ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">Usia Ibu</span>: 
                <span>{{ $skrining->ibuHamil->usia ? $skrining->ibuHamil->usia . ' Tahun' : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">HPHT</span>: 
                <span>{{ $skrining->ibuHamil->hpht ? $skrining->ibuHamil->hpht->format('d/m/Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">Taksiran Lahir</span>: 
                <strong>{{ $skrining->ibuHamil->taksiran_persalinan ? $skrining->ibuHamil->taksiran_persalinan->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">Paritas Obstetri</span>: 
                <span>G:{{ $skrining->ibuHamil->gravida }} P:{{ $skrining->ibuHamil->para }} A:{{ $skrining->ibuHamil->abortus }} (Hidup: {{ $skrining->ibuHamil->jumlah_anak_hidup }})</span>
            </div>
            <div>
                <span class="text-slate-500 inline-block w-28">TB / BB / Gol. Darah</span>: 
                <span>{{ $skrining->ibuHamil->tinggi_badan ?? '-' }} cm / {{ $skrining->ibuHamil->berat_badan ?? '-' }} kg / ({{ $skrining->ibuHamil->golongan_darah ?? '-' }})</span>
            </div>
        </div>

        <!-- Tabel SPR 23 Pertanyaan -->
        <table class="w-full border-collapse border border-slate-400 text-[11px] mb-4">
            <thead>
                <tr class="bg-slate-200 text-slate-800 font-bold border-b border-slate-400 text-left">
                    <th class="p-1.5 border-r border-slate-400 w-10 text-center">No</th>
                    <th class="p-1.5 border-r border-slate-400">Masalah / Faktor Risiko</th>
                    <th class="p-1.5 border-r border-slate-400 w-16 text-center">Skor Standar</th>
                    <th class="p-1.5 w-16 text-center">Hasil</th>
                </tr>
            </thead>
            <tbody>
                <!-- Skor Dasar -->
                <tr class="bg-emerald-50/60 font-semibold border-b border-slate-300">
                    <td class="p-1.5 border-r border-slate-400 text-center">-</td>
                    <td class="p-1.5 border-r border-slate-400 font-bold">Skor Awal Ibu Hamil (Standar Dasar)</td>
                    <td class="p-1.5 border-r border-slate-400 text-center font-bold">2</td>
                    <td class="p-1.5 text-center font-bold text-emerald-800">2</td>
                </tr>

                <!-- Kelompok I -->
                <tr class="bg-slate-100 font-bold text-slate-700 border-b border-slate-300">
                    <td colspan="4" class="p-1.5 uppercase">I. Faktor Risiko: Ada Potensi Gawat Obstetri (APGO)</td>
                </tr>
                @foreach($kelompokI as $detail)
                    <tr class="border-b border-slate-200 {{ $detail->jawaban ? 'bg-amber-50 font-bold' : '' }}">
                        <td class="p-1.5 border-r border-slate-400 text-center font-mono">{{ $detail->pertanyaanSpr->nomor }}</td>
                        <td class="p-1.5 border-r border-slate-400">{{ $detail->pertanyaanSpr->pertanyaan }}</td>
                        <td class="p-1.5 border-r border-slate-400 text-center">{{ $detail->pertanyaanSpr->skor }}</td>
                        <td class="p-1.5 text-center font-bold {{ $detail->jawaban ? 'text-amber-800' : 'text-slate-400' }}">
                            {{ $detail->jawaban ? $detail->skor : '-' }}
                        </td>
                    </tr>
                @endforeach

                <!-- Kelompok II -->
                <tr class="bg-slate-100 font-bold text-slate-700 border-b border-slate-300">
                    <td colspan="4" class="p-1.5 uppercase">II. Faktor Risiko: Ada Gawat Obstetri (AGO)</td>
                </tr>
                @foreach($kelompokII as $detail)
                    <tr class="border-b border-slate-200 {{ $detail->jawaban ? 'bg-amber-50 font-bold' : '' }}">
                        <td class="p-1.5 border-r border-slate-400 text-center font-mono">{{ $detail->pertanyaanSpr->nomor }}</td>
                        <td class="p-1.5 border-r border-slate-400">{{ $detail->pertanyaanSpr->pertanyaan }}</td>
                        <td class="p-1.5 border-r border-slate-400 text-center">{{ $detail->pertanyaanSpr->skor }}</td>
                        <td class="p-1.5 text-center font-bold {{ $detail->jawaban ? 'text-amber-800' : 'text-slate-400' }}">
                            {{ $detail->jawaban ? $detail->skor : '-' }}
                        </td>
                    </tr>
                @endforeach

                <!-- Kelompok III -->
                <tr class="bg-slate-100 font-bold text-slate-700 border-b border-slate-300">
                    <td colspan="4" class="p-1.5 uppercase">III. Faktor Risiko: Ada Gawat Darurat Obstetri (AGDO)</td>
                </tr>
                @foreach($kelompokIII as $detail)
                    <tr class="border-b border-slate-200 {{ $detail->jawaban ? 'bg-rose-50 font-bold' : '' }}">
                        <td class="p-1.5 border-r border-slate-400 text-center font-mono">{{ $detail->pertanyaanSpr->nomor }}</td>
                        <td class="p-1.5 border-r border-slate-400">{{ $detail->pertanyaanSpr->pertanyaan }}</td>
                        <td class="p-1.5 border-r border-slate-400 text-center">{{ $detail->pertanyaanSpr->skor }}</td>
                        <td class="p-1.5 text-center font-bold {{ $detail->jawaban ? 'text-rose-800' : 'text-slate-400' }}">
                            {{ $detail->jawaban ? $detail->skor : '-' }}
                        </td>
                    </tr>
                @endforeach

                <!-- Total Score Row -->
                <tr class="bg-slate-200 font-extrabold text-sm border-t-2 border-slate-900">
                    <td colspan="2" class="p-2 border-r border-slate-400 text-right uppercase">
                        JUMLAH TOTAL SKOR RISIKO KEHAMILAN :
                    </td>
                    <td colspan="2" class="p-2 text-center text-slate-900 text-base">
                        {{ $skrining->total_skor }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Kesimpulan Kategori & Rujukan -->
        <div class="border-2 border-slate-800 p-3 rounded-lg text-xs mb-6">
            <div class="flex items-center justify-between border-b border-slate-300 pb-2 mb-2">
                <span class="font-bold uppercase tracking-wider text-slate-700">Kategori Tingkat Risiko Kehamilan:</span>
                <span class="font-extrabold text-sm px-3 py-0.5 rounded border border-slate-800
                    {{ $skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST ? 'bg-rose-100 text-rose-900' : ($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900') }}">
                    {{ $skrining->kategori_risiko->label() }} ({{ $skrining->kategori_risiko->value }})
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="font-bold block text-slate-700">Rencana Penolong Persalinan:</span>
                    <span>
                        @if($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST)
                            Dokter Spesialis Kebidanan (SpOG)
                        @elseif($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT)
                            Bidan didampingi Dokter
                        @else
                            Bidan
                        @endif
                    </span>
                </div>
                <div>
                    <span class="font-bold block text-slate-700">Rencana Tempat Persalinan:</span>
                    <span>
                        @if($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRST)
                            Rumah Sakit (RS) Rujukan Segera
                        @elseif($skrining->kategori_risiko === \App\Enums\KategoriRisiko::KRT)
                            Puskesmas PONED / Rumah Sakit
                        @else
                            Polindes / Poskesdes / BPM / Puskesmas
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs pt-4">
            <div>
                <p class="text-slate-500">Mengetahui,</p>
                <p class="font-semibold text-slate-800">Ibu Hamil / Keluarga</p>
                <div class="h-16"></div>
                <p class="border-t border-slate-400 inline-block px-8 pt-1">
                    ( {{ $skrining->ibuHamil->nama }} )
                </p>
            </div>
            <div>
                <p class="text-slate-500">{{ $skrining->ibuHamil->desa?->nama }}, {{ $skrining->tanggal_skrining->format('d/m/Y') }}</p>
                <p class="font-semibold text-slate-800">Pemeriksa ({{ $skrining->user?->role->label() }})</p>
                <div class="h-16"></div>
                <p class="border-t border-slate-400 inline-block px-8 pt-1 font-bold">
                    ( {{ $skrining->user?->name }} )
                </p>
            </div>
        </div>

    </div>

</body>
</html>

