@extends('layouts.app', ['title' => 'Pendaftaran Ibu Hamil', 'header' => 'Pendaftaran Ibu Hamil Baru'])

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
    hpht: '{{ old('hpht') }}',
    tp: '{{ old('taksiran_persalinan') }}',
    tanggalLahir: '{{ old('tanggal_lahir') }}',
    tb: '{{ old('tinggi_badan') }}',
    bb: '{{ old('berat_badan') }}',
    lila: '{{ old('lila') }}',
    get usia() {
        if (!this.tanggalLahir) return null;
        const birth = new Date(this.tanggalLahir);
        if (isNaN(birth.getTime())) return null;
        const diff = Date.now() - birth.getTime();
        const ageDate = new Date(diff);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    },
    get imt() {
        const tbNum = parseFloat(this.tb);
        const bbNum = parseFloat(this.bb);
        if (isNaN(tbNum) || isNaN(bbNum) || tbNum <= 0 || bbNum <= 0) return null;
        const meter = tbNum / 100;
        return (bbNum / (meter * meter)).toFixed(1);
    },
    get statusGizi() {
        const imtNum = parseFloat(this.imt);
        if (isNaN(imtNum)) return '{{ old('status_gizi', 'baik') }}';
        if (imtNum < 18.5) return 'kurang';
        if (imtNum <= 24.9) return 'baik';
        if (imtNum <= 29.9) return 'lebih';
        return 'obesitas';
    },
    get statusGiziLabel() {
        const imtNum = parseFloat(this.imt);
        if (isNaN(imtNum)) return 'Masukkan Tinggi & Berat Badan';
        const lilaNum = parseFloat(this.lila);
        const isKek = !isNaN(lilaNum) && lilaNum < 23.5;
        if (imtNum < 18.5) return isKek ? 'Gizi Kurang (Risiko KEK)' : 'Gizi Kurang (Underweight)';
        if (imtNum <= 24.9) return 'Gizi Baik (Normal)';
        if (imtNum <= 29.9) return 'Gizi Lebih (Overweight)';
        return 'Obesitas';
    },
    get statusGiziColor() {
        const imtNum = parseFloat(this.imt);
        if (isNaN(imtNum)) return 'bg-slate-50 text-slate-700 border-slate-200';
        if (imtNum < 18.5) return 'bg-amber-50 text-amber-900 border-amber-300';
        if (imtNum <= 24.9) return 'bg-emerald-50 text-emerald-900 border-emerald-300';
        if (imtNum <= 29.9) return 'bg-orange-50 text-orange-900 border-orange-300';
        return 'bg-rose-50 text-rose-900 border-rose-300';
    },
    calculateTP() {
        if (!this.hpht) return;
        const d = new Date(this.hpht);
        if (isNaN(d.getTime())) return;
        d.setDate(d.getDate() + 280);
        const iso = d.toISOString().split('T')[0];
        this.tp = iso;
        const tpEl = document.getElementById('taksiran_persalinan');
        if (tpEl && tpEl._flatpickr) {
            tpEl._flatpickr.setDate(iso, true);
        }
    }
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('ibu-hamil.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Data Ibu Hamil
            </a>
            <h2 class="text-xl font-bold text-slate-800">Formulir Data Pribadi Ibu Hamil</h2>
            <p class="text-xs text-slate-500">Lengkapi data identitas, biometrik fisik, dan riwayat obstetri kehamilan</p>
        </div>

        <form action="{{ route('ibu-hamil.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Identitas Pribadi -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    1. Identitas Ibu & Pasangan
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Lengkap Ibu Hamil <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" required value="{{ old('nama') }}"
                               placeholder="Contoh: Ny. Siti Maryam"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            NIK (Nomor Induk Kependudukan 16 Digit) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nik" name="nik" required value="{{ old('nik') }}" maxlength="16" minlength="16" pattern="[0-9]{16}"
                               placeholder="3201xxxxxxxxxxxx (wajib 16 digit)"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                        <span class="text-[10px] text-slate-500 mt-1 block">Wajib 16 digit angka kependudukan untuk sinkronisasi SatuSehat & BPJS.</span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="tanggal_lahir" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Tanggal Lahir Ibu
                            </label>
                            <span x-show="usia !== null" x-cloak class="text-[11px] font-bold text-teal-700">
                                Usia: <span x-text="usia"></span> Tahun
                            </span>
                        </div>
                        <div class="relative mt-1.5">
                            <input type="text" id="tanggal_lahir" name="tanggal_lahir" x-model="tanggalLahir"
                                   placeholder="HH-BB-TTTT (contoh: 15-08-1998)"
                                   class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-teal-600 mt-1 block font-medium">Format: Hari-Bulan-Tahun</span>
                    </div>

                    <div>
                        <label for="nama_suami" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Suami
                        </label>
                        <input type="text" id="nama_suami" name="nama_suami" value="{{ old('nama_suami') }}"
                               placeholder="Contoh: Tn. Ahmad Fauzi"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            No Handphone / WhatsApp
                        </label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                               placeholder="08xxxxxxxxxx"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Wilayah Desa <span class="text-rose-500">*</span>
                        </label>
                        @if(auth()->user()->isBidan())
                            <select id="desa_id" name="desa_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-teal-300 bg-white text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium">
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ (old('desa_id', auth()->user()->desa_id) == $d->id) ? 'selected' : '' }}>
                                        {{ $d->nama }} (Kec. {{ $d->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-teal-700 mt-1 block">Bidan Desa dapat memilih kelurahan/desa binaan di wilayah kerja Puskesmas</span>
                        @elseif(auth()->user()->isKader())
                            <input type="hidden" name="desa_id" value="{{ auth()->user()->desa_id }}" />
                            <div class="mt-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold">
                                {{ auth()->user()->desa?->nama }}
                            </div>
                        @else
                            <select id="desa_id" name="desa_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="">-- Pilih Wilayah Desa --</option>
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ (old('desa_id', request('desa_id')) == $d->id) ? 'selected' : '' }}>
                                        {{ $d->nama }} ({{ $d->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Tempat Tinggal Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="2"
                              placeholder="RT/RW, Kampung/Dusun, Desa..."
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('alamat') }}</textarea>
                </div>
            </div>

            <!-- Fisik & Obstetri -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    2. Data Fisik & Riwayat Kehamilan (Obstetri)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tinggi_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Tinggi Badan (cm)
                        </label>
                        <input type="number" step="0.1" id="tinggi_badan" name="tinggi_badan" x-model="tb"
                               placeholder="Contoh: 155"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-semibold" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">TB ≤ 145 cm = Faktor risiko SPR</span>
                    </div>

                    <div>
                        <label for="berat_badan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Berat Badan (kg)
                        </label>
                        <input type="number" step="0.1" id="berat_badan" name="berat_badan" x-model="bb"
                               placeholder="Contoh: 55"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-semibold" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Digunakan menghitung IMT</span>
                    </div>

                    <div>
                        <label for="golongan_darah" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Golongan Darah
                        </label>
                        <select id="golongan_darah" name="golongan_darah"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Belum Diketahui --</option>
                            <option value="A" {{ old('golongan_darah') === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah') === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah') === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah') === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>

                    <!-- Box Kalkulasi IMT & Status Gizi Otomatis -->
                    <div class="sm:col-span-3 p-4 rounded-2xl border transition-all" :class="statusGiziColor">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider block">Indeks Massa Tubuh (IMT) & Status Gizi Otomatis</span>
                                <div class="text-xs mt-0.5">
                                    Skor IMT: <strong class="text-sm font-extrabold font-mono" x-text="imt ? (imt + ' kg/m²') : 'Menunggu input TB & BB'"></strong>
                                    &bull; Kategori: <strong x-text="statusGiziLabel"></strong>
                                </div>
                            </div>
                            <template x-if="imt">
                                <span class="text-[11px] px-3 py-1 rounded-full bg-white/90 font-bold border shadow-2xs"
                                      :class="imt < 18.5 ? 'text-amber-800 border-amber-300' : (imt <= 24.9 ? 'text-emerald-800 border-emerald-300' : 'text-rose-800 border-rose-300')"
                                      x-text="imt < 18.5 ? '⚠️ Gizi Kurang' : (imt <= 24.9 ? '✓ Berat Badan Normal' : (imt <= 29.9 ? '⚠️ Gizi Lebih (Overweight)' : '⚠️ Obesitas'))">
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="hpht" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            HPHT (Hari Pertama Haid Terakhir)
                        </label>
                        <div class="relative mt-1.5">
                            <input type="text" id="hpht" name="hpht" x-model="hpht" @change="calculateTP()"
                                   placeholder="HH-BB-TTTT (contoh: 01-02-2026)"
                                   class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-teal-600 mt-1 block font-medium">Format: Hari-Bulan-Tahun</span>
                    </div>

                    <div>
                        <label for="taksiran_persalinan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span>Taksiran Persalinan (HPL)</span>
                            <span class="text-[10px] text-teal-600 font-normal">Otomatis terhitung dari HPHT</span>
                        </label>
                        <div class="relative mt-1.5">
                            <input type="text" id="taksiran_persalinan" name="taksiran_persalinan" x-model="tp"
                                   placeholder="HH-BB-TTTT (contoh: 08-11-2026)"
                                   class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium bg-teal-50/30" />
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <span class="text-[10px] text-teal-600 mt-1 block font-medium">Format: Hari-Bulan-Tahun</span>
                    </div>
                </div>

                <!-- Paritas G - P - A -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold text-slate-700 block mb-2">Riwayat Paritas Obstetri:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Gravida (Hamil ke-)</label>
                            <input type="number" min="0" name="gravida" value="{{ old('gravida', 1) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Para (Pernah Melahirkan)</label>
                            <input type="number" min="0" name="para" value="{{ old('para', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Abortus (Keguguran)</label>
                            <input type="number" min="0" name="abortus" value="{{ old('abortus', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Jumlah Anak Hidup</label>
                            <input type="number" min="0" name="jumlah_anak_hidup" value="{{ old('jumlah_anak_hidup', 0) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pemeriksaan Fisik Ibu Hamil (ANC) -->
            <div>
                <div class="flex items-center justify-between border-b border-teal-100 pb-2 mb-4">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700">
                            3. Pemeriksaan Fisik Ibu Hamil (Standar ANC)
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Parameter klinis tanda vital, nutrisi gizi, hemoglobin, dan janin</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                        Pemeriksaan Klinis
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="tekanan_darah_sistol" class="block text-xs font-semibold text-slate-700">
                            TD Sistol (mmHg)
                        </label>
                        <input type="number" id="tekanan_darah_sistol" name="tekanan_darah_sistol" value="{{ old('tekanan_darah_sistol') }}"
                               placeholder="Contoh: 120"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal: 90 - 120 mmHg</span>
                    </div>

                    <div>
                        <label for="tekanan_darah_diastol" class="block text-xs font-semibold text-slate-700">
                            TD Diastol (mmHg)
                        </label>
                        <input type="number" id="tekanan_darah_diastol" name="tekanan_darah_diastol" value="{{ old('tekanan_darah_diastol') }}"
                               placeholder="Contoh: 80"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal: 60 - 80 mmHg</span>
                    </div>

                    <div>
                        <label for="lila" class="block text-xs font-semibold text-slate-700">
                            LiLA (Lingkar Lengan) (cm)
                        </label>
                        <input type="number" step="0.1" id="lila" name="lila" x-model="lila" value="{{ old('lila') }}"
                               placeholder="Contoh: 24.5"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-rose-500 font-medium mt-0.5 block">&lt; 23.5 cm = Risiko KEK</span>
                    </div>

                    <div>
                        <label for="hemoglobin" class="block text-xs font-semibold text-slate-700">
                            Hemoglobin (Hb) (g/dL)
                        </label>
                        <input type="number" step="0.1" id="hemoglobin" name="hemoglobin" value="{{ old('hemoglobin') }}"
                               placeholder="Contoh: 11.5"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-amber-600 font-medium mt-0.5 block">&lt; 11 g/dL = Anemia</span>
                    </div>

                    <div>
                        <label for="tfu" class="block text-xs font-semibold text-slate-700">
                            Tinggi Fundus Uteri (TFU) (cm)
                        </label>
                        <input type="number" step="0.1" id="tfu" name="tfu" value="{{ old('tfu') }}"
                               placeholder="Contoh: 28"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="djj" class="block text-xs font-semibold text-slate-700">
                            Denyut Jantung Janin (DJJ) (bpm)
                        </label>
                        <input type="number" id="djj" name="djj" value="{{ old('djj') }}"
                               placeholder="Contoh: 140"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal: 120 - 160 bpm</span>
                    </div>

                    <div>
                        <label for="status_imunisasi_tt" class="block text-xs font-semibold text-slate-700">
                            Status Imunisasi TT
                        </label>
                        <select id="status_imunisasi_tt" name="status_imunisasi_tt"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Status TT --</option>
                            <option value="TT0" {{ old('status_imunisasi_tt') === 'TT0' ? 'selected' : '' }}>TT0 (Belum pernah imunisasi)</option>
                            <option value="TT1" {{ old('status_imunisasi_tt') === 'TT1' ? 'selected' : '' }}>TT1 (Langkah 1)</option>
                            <option value="TT2" {{ old('status_imunisasi_tt') === 'TT2' ? 'selected' : '' }}>TT2 (Perlindungan 3 tahun)</option>
                            <option value="TT3" {{ old('status_imunisasi_tt') === 'TT3' ? 'selected' : '' }}>TT3 (Perlindungan 5 tahun)</option>
                            <option value="TT4" {{ old('status_imunisasi_tt') === 'TT4' ? 'selected' : '' }}>TT4 (Perlindungan 10 tahun)</option>
                            <option value="TT5" {{ old('status_imunisasi_tt') === 'TT5' ? 'selected' : '' }}>TT5 (Perlindungan seumur hidup)</option>
                        </select>
                    </div>

                    <div>
                        <label for="status_gizi" class="block text-xs font-semibold text-slate-700 flex items-center justify-between">
                            <span>Status Gizi</span>
                            <span class="text-[10px] text-teal-600 font-normal">Otomatis dari IMT & TB/BB</span>
                        </label>
                        <input type="hidden" name="status_gizi" :value="statusGizi" />
                        <input type="hidden" name="imt" :value="imt" />
                        <div class="mt-1.5 px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-800 text-xs sm:text-sm font-semibold flex items-center justify-between">
                            <span x-text="statusGiziLabel"></span>
                            <span class="text-[11px] text-teal-700 font-mono font-bold" x-show="imt" x-text="'IMT ' + imt"></span>
                        </div>
                    </div>

                    <div>
                        <label for="refleks_patela" class="block text-xs font-semibold text-slate-700">
                            Refleks Patela
                        </label>
                        <select id="refleks_patela" name="refleks_patela"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih --</option>
                            <option value="positif" {{ old('refleks_patela', 'positif') === 'positif' ? 'selected' : '' }}>Positif (+) [Normal]</option>
                            <option value="negatif" {{ old('refleks_patela') === 'negatif' ? 'selected' : '' }}>Negatif (-) [Abnormal]</option>
                        </select>
                    </div>

                    <div>
                        <label for="edema" class="block text-xs font-semibold text-slate-700">
                            Edema / Pembengkakan
                        </label>
                        <select id="edema" name="edema"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Kondisi Edema --</option>
                            <option value="tidak_ada" {{ old('edema', 'tidak_ada') === 'tidak_ada' ? 'selected' : '' }}>Tidak Ada (Normal)</option>
                            <option value="tungkai" {{ old('edema') === 'tungkai' ? 'selected' : '' }}>Edema Tungkai / Kaki</option>
                            <option value="seluruh_tubuh" {{ old('edema') === 'seluruh_tubuh' ? 'selected' : '' }}>Edema Wajah / Seluruh Tubuh (Preeklampsia)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="kondisi_umum" class="block text-xs font-semibold text-slate-700">
                            Kondisi Umum Ibu Hamil
                        </label>
                        <select id="kondisi_umum" name="kondisi_umum"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Kondisi Umum --</option>
                            <option value="baik" {{ old('kondisi_umum', 'baik') === 'baik' ? 'selected' : '' }}>Baik (Compos Mentis, Sadar Penuh, Aktif)</option>
                            <option value="sedang" {{ old('kondisi_umum') === 'sedang' ? 'selected' : '' }}>Sedang (Lemas, Mengeluh Pusing/Mual)</option>
                            <option value="lemah" {{ old('kondisi_umum') === 'lemah' ? 'selected' : '' }}>Lemah (Pucat, Butuh Istirahat Tirah Baring/Rujuk)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ibu-hamil.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan & Lanjutkan ke Skrining
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

