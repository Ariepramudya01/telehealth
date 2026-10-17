@extends('layouts.app', ['title' => 'Edit Ibu Hamil: ' . $ibuHamil->nama, 'header' => 'Edit Data Ibu Hamil'])

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
    hpht: '{{ old('hpht', $ibuHamil->hpht?->format('Y-m-d')) }}',
    tp: '{{ old('taksiran_persalinan', $ibuHamil->taksiran_persalinan?->format('Y-m-d')) }}',
    tanggalLahir: '{{ old('tanggal_lahir', $ibuHamil->tanggal_lahir?->format('Y-m-d')) }}',
    tb: '{{ old('tinggi_badan', $ibuHamil->tinggi_badan) }}',
    bb: '{{ old('berat_badan', $ibuHamil->berat_badan) }}',
    lila: '{{ old('lila', $ibuHamil->lila) }}',
    get usia() {
        if (!this.tanggalLahir) return null;
        const birth = new Date(this.tanggalLahir);
        if (isNaN(birth.getTime())) return null;
        const now = new Date();
        let age = now.getFullYear() - birth.getFullYear();
        const m = now.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) age--;
        return age >= 0 ? age : null;
    },
    get imt() {
        const h = parseFloat(this.tb) / 100;
        const w = parseFloat(this.bb);
        if (!h || !w || h <= 0 || w <= 0) return null;
        return (w / (h * h)).toFixed(1);
    },
    get statusGizi() {
        const imtNum = parseFloat(this.imt);
        if (isNaN(imtNum)) return '{{ old('status_gizi', $ibuHamil->status_gizi ?? '') }}';
        if (imtNum < 18.5) return 'kurang';
        if (imtNum <= 24.9) return 'baik';
        if (imtNum <= 29.9) return 'lebih';
        return 'obesitas';
    },
    get statusGiziLabel() {
        const imtNum = parseFloat(this.imt);
        if (isNaN(imtNum)) {
            const current = '{{ old('status_gizi', $ibuHamil->status_gizi) }}';
            if (current === 'baik') return 'Gizi Baik (Normal)';
            if (current === 'kurang') return 'Gizi Kurang (Underweight)';
            if (current === 'lebih') return 'Gizi Lebih (Overweight)';
            if (current === 'obesitas') return 'Obesitas';
            return 'Masukkan Tinggi & Berat Badan';
        }
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
        this.tp = d.toISOString().split('T')[0];
    }
}">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="mb-6">
            <a href="{{ route('ibu-hamil.show', $ibuHamil) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Rincian Ibu Hamil
            </a>
            <h2 class="text-xl font-bold text-slate-800">Edit Data: {{ $ibuHamil->nama }}</h2>
            <p class="text-xs text-slate-500">Perbarui informasi identitas, biometrik fisik, atau riwayat kehamilan</p>
        </div>

        <form action="{{ route('ibu-hamil.update', $ibuHamil) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

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
                        <input type="text" id="nama" name="nama" required value="{{ old('nama', $ibuHamil->nama) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nik" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            NIK (Nomor Induk Kependudukan)
                        </label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik', $ibuHamil->nik) }}" maxlength="16"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
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
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" x-model="tanggalLahir"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="nama_suami" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Nama Suami
                        </label>
                        <input type="text" id="nama_suami" name="nama_suami" value="{{ old('nama_suami', $ibuHamil->nama_suami) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                            No Handphone / WhatsApp
                        </label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $ibuHamil->no_hp) }}"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    @if(auth()->user()->isAdmin())
                        <div>
                            <label for="desa_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Wilayah Desa <span class="text-rose-500">*</span>
                            </label>
                            <select id="desa_id" name="desa_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                @foreach($desas as $d)
                                    <option value="{{ $d->id }}" {{ old('desa_id', $ibuHamil->desa_id) == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama }} ({{ $d->kecamatan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <label for="alamat" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Lengkap
                    </label>
                    <textarea id="alamat" name="alamat" rows="2"
                              class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">{{ old('alamat', $ibuHamil->alamat) }}</textarea>
                </div>
            </div>

            <!-- Fisik & Obstetri -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 border-b border-teal-100 pb-2 mb-4">
                    2. Data Fisik & Riwayat Kehamilan
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
                            <option value="A" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah', $ibuHamil->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
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
                        <input type="date" id="hpht" name="hpht" x-model="hpht" @change="calculateTP()"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="taksiran_persalinan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center justify-between">
                            <span>Taksiran Persalinan (HPL)</span>
                            <span class="text-[10px] text-teal-600 font-normal">Otomatis terhitung dari HPHT</span>
                        </label>
                        <input type="date" id="taksiran_persalinan" name="taksiran_persalinan" x-model="tp"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm bg-teal-50/30" />
                    </div>
                </div>

                <!-- Paritas G - P - A -->
                <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold text-slate-700 block mb-2">Riwayat Paritas Obstetri:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Gravida</label>
                            <input type="number" min="0" name="gravida" value="{{ old('gravida', $ibuHamil->gravida) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Para</label>
                            <input type="number" min="0" name="para" value="{{ old('para', $ibuHamil->para) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Abortus</label>
                            <input type="number" min="0" name="abortus" value="{{ old('abortus', $ibuHamil->abortus) }}"
                                   class="mt-1 block w-full px-3 py-2 rounded-xl border border-slate-300 text-xs text-center font-bold" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600">Anak Hidup</label>
                            <input type="number" min="0" name="jumlah_anak_hidup" value="{{ old('jumlah_anak_hidup', $ibuHamil->jumlah_anak_hidup) }}"
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
                        <p class="text-[11px] text-slate-500 mt-0.5">Parameter klinis tanda vital, biometri gizi, hemoglobin, dan janin</p>
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
                        <input type="number" id="tekanan_darah_sistol" name="tekanan_darah_sistol" value="{{ old('tekanan_darah_sistol', $ibuHamil->tekanan_darah_sistol) }}"
                               placeholder="Contoh: 120"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal: 90 - 120 mmHg</span>
                    </div>

                    <div>
                        <label for="tekanan_darah_diastol" class="block text-xs font-semibold text-slate-700">
                            TD Diastol (mmHg)
                        </label>
                        <input type="number" id="tekanan_darah_diastol" name="tekanan_darah_diastol" value="{{ old('tekanan_darah_diastol', $ibuHamil->tekanan_darah_diastol) }}"
                               placeholder="Contoh: 80"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Normal: 60 - 80 mmHg</span>
                    </div>

                    <div>
                        <label for="lila" class="block text-xs font-semibold text-slate-700">
                            LiLA (Lingkar Lengan) (cm)
                        </label>
                        <input type="number" step="0.1" id="lila" name="lila" x-model="lila"
                               placeholder="Contoh: 24.5"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-rose-500 font-medium mt-0.5 block">&lt; 23.5 cm = Risiko KEK</span>
                    </div>

                    <div>
                        <label for="hemoglobin" class="block text-xs font-semibold text-slate-700">
                            Hemoglobin (Hb) (g/dL)
                        </label>
                        <input type="number" step="0.1" id="hemoglobin" name="hemoglobin" value="{{ old('hemoglobin', $ibuHamil->hemoglobin) }}"
                               placeholder="Contoh: 11.5"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        <span class="text-[10px] text-amber-600 font-medium mt-0.5 block">&lt; 11 g/dL = Anemia</span>
                    </div>

                    <div>
                        <label for="tfu" class="block text-xs font-semibold text-slate-700">
                            Tinggi Fundus Uteri (TFU) (cm)
                        </label>
                        <input type="number" step="0.1" id="tfu" name="tfu" value="{{ old('tfu', $ibuHamil->tfu) }}"
                               placeholder="Contoh: 28"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                    </div>

                    <div>
                        <label for="djj" class="block text-xs font-semibold text-slate-700">
                            Denyut Jantung Janin (DJJ) (bpm)
                        </label>
                        <input type="number" id="djj" name="djj" value="{{ old('djj', $ibuHamil->djj) }}"
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
                            <option value="TT0" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT0' ? 'selected' : '' }}>TT0 (Belum pernah imunisasi)</option>
                            <option value="TT1" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT1' ? 'selected' : '' }}>TT1 (Langkah 1)</option>
                            <option value="TT2" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT2' ? 'selected' : '' }}>TT2 (Perlindungan 3 tahun)</option>
                            <option value="TT3" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT3' ? 'selected' : '' }}>TT3 (Perlindungan 5 tahun)</option>
                            <option value="TT4" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT4' ? 'selected' : '' }}>TT4 (Perlindungan 10 tahun)</option>
                            <option value="TT5" {{ old('status_imunisasi_tt', $ibuHamil->status_imunisasi_tt) === 'TT5' ? 'selected' : '' }}>TT5 (Perlindungan seumur hidup)</option>
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
                            <option value="positif" {{ old('refleks_patela', $ibuHamil->refleks_patela ?? 'positif') === 'positif' ? 'selected' : '' }}>Positif (+) [Normal]</option>
                            <option value="negatif" {{ old('refleks_patela', $ibuHamil->refleks_patela) === 'negatif' ? 'selected' : '' }}>Negatif (-) [Abnormal]</option>
                        </select>
                    </div>

                    <div>
                        <label for="edema" class="block text-xs font-semibold text-slate-700">
                            Edema / Pembengkakan
                        </label>
                        <select id="edema" name="edema"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Kondisi Edema --</option>
                            <option value="tidak_ada" {{ old('edema', $ibuHamil->edema ?? 'tidak_ada') === 'tidak_ada' ? 'selected' : '' }}>Tidak Ada (Normal)</option>
                            <option value="tungkai" {{ old('edema', $ibuHamil->edema) === 'tungkai' ? 'selected' : '' }}>Edema Tungkai / Kaki</option>
                            <option value="seluruh_tubuh" {{ old('edema', $ibuHamil->edema) === 'seluruh_tubuh' ? 'selected' : '' }}>Edema Wajah / Seluruh Tubuh (Preeklampsia)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="kondisi_umum" class="block text-xs font-semibold text-slate-700">
                            Kondisi Umum Ibu Hamil
                        </label>
                        <select id="kondisi_umum" name="kondisi_umum"
                                class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                            <option value="">-- Pilih Kondisi Umum --</option>
                            <option value="baik" {{ old('kondisi_umum', $ibuHamil->kondisi_umum ?? 'baik') === 'baik' ? 'selected' : '' }}>Baik (Compos Mentis, Sadar Penuh, Aktif)</option>
                            <option value="sedang" {{ old('kondisi_umum', $ibuHamil->kondisi_umum) === 'sedang' ? 'selected' : '' }}>Sedang (Lemas, Mengeluh Pusing/Mual)</option>
                            <option value="lemah" {{ old('kondisi_umum', $ibuHamil->kondisi_umum) === 'lemah' ? 'selected' : '' }}>Lemah (Pucat, Butuh Rujukan Segera)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Koordinat Lokasi GPS & Deteksi Google (Desa, Kecamatan, Kabupaten) -->
            <div x-data="{
                geoLoading: false,
                geoError: '',
                searchQuery: '',
                searchLoading: false,
                detectedGoogle: null,

                getCurrentLocationGoogle() {
                    if (!navigator.geolocation) {
                        this.geoError = 'Browser Anda tidak mendukung deteksi lokasi GPS.';
                        return;
                    }
                    this.geoLoading = true;
                    this.geoError = '';
                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            document.getElementById('lat_input_edit').value = lat.toFixed(7);
                            document.getElementById('lng_input_edit').value = lng.toFixed(7);
                            await this.fetchGoogleLocation(lat, lng);
                            this.geoLoading = false;
                        },
                        (err) => {
                            this.geoLoading = false;
                            this.geoError = 'Gagal mendeteksi lokasi: ' + err.message;
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                },

                async searchGoogleLocation() {
                    if (!this.searchQuery.trim()) return;
                    this.searchLoading = true;
                    this.geoError = '';
                    try {
                        const res = await fetch('/api/locations/geocode?address=' + encodeURIComponent(this.searchQuery));
                        const data = await res.json();
                        if (data.success && data.latitude && data.longitude) {
                            document.getElementById('lat_input_edit').value = Number(data.latitude).toFixed(7);
                            document.getElementById('lng_input_edit').value = Number(data.longitude).toFixed(7);
                            this.applyGoogleData(data);
                        } else {
                            this.geoError = data.message || 'Lokasi tidak ditemukan di Google.';
                        }
                    } catch (e) {
                        this.geoError = 'Koneksi ke layanan Google Geocoding gagal.';
                    } finally {
                        this.searchLoading = false;
                    }
                },

                async fetchGoogleLocation(lat, lng) {
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
                    const alamatEl = document.getElementById('alamat');
                    if (alamatEl && (!alamatEl.value || alamatEl.value.trim() === '')) {
                        alamatEl.value = data.alamat_lengkap || '';
                    }
                    const desaSelect = document.getElementById('desa_id');
                    if (desaSelect && data.matched_desa_id) {
                        desaSelect.value = data.matched_desa_id;
                    }
                }
            }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-teal-100 pb-2 mb-4">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700">
                            4. Lokasi Tempat Tinggal (Diambil Langsung via Google)
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Mengambil data Desa/Kelurahan, Kecamatan, dan Kabupaten secara otomatis dari Google</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="getCurrentLocationGoogle()" :disabled="geoLoading"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition-all shadow-xs disabled:opacity-50">
                            <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': geoLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="geoLoading ? 'Mendeteksi via Google...' : '📍 Ambil Lokasi Saya (Google GPS)'"></span>
                        </button>
                    </div>
                </div>

                <!-- Input Pencarian Google Alternatif -->
                <div class="mb-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        🔍 Atau Cari Wilayah / Alamat via Google:
                    </label>
                    <div class="flex gap-2">
                        <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchGoogleLocation()"
                               placeholder="Ketik nama desa, puskesmas, kampung, atau jalan (contoh: Sukamaju Ciomas Bogor)..."
                               class="flex-1 px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white" />
                        <button type="button" @click="searchGoogleLocation()" :disabled="searchLoading"
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shrink-0 disabled:opacity-50">
                            <span x-text="searchLoading ? 'Mencari...' : 'Cari di Google'"></span>
                        </button>
                    </div>
                </div>

                <div x-show="geoError" x-cloak class="mb-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs" x-text="geoError"></div>

                <!-- Hasil Parsing Google: Desa/Kelurahan, Kecamatan, Kabupaten -->
                <div x-show="detectedGoogle" x-cloak class="mb-4 p-4 rounded-2xl bg-gradient-to-r from-teal-50 to-emerald-50 border border-teal-200 shadow-2xs">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-700 text-white">
                                Google Geocoding
                            </span>
                            <span class="text-xs text-teal-900 font-bold">Hasil Pengambilan Lokasi Google:</span>
                        </div>
                        <template x-if="detectedGoogle?.matched_desa_nama">
                            <span class="text-[11px] text-emerald-800 font-bold bg-emerald-100/80 px-2.5 py-0.5 rounded-full border border-emerald-300">
                                ✓ Terhubung: <span x-text="detectedGoogle?.matched_desa_nama"></span>
                            </span>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                        <div class="bg-white p-3 rounded-xl border border-teal-200/80 shadow-2xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">1. Desa / Kelurahan</span>
                            <strong class="text-teal-900 text-sm block mt-0.5" x-text="detectedGoogle?.desa || '-'"></strong>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-teal-200/80 shadow-2xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">2. Kecamatan</span>
                            <strong class="text-slate-800 text-sm block mt-0.5" x-text="detectedGoogle?.kecamatan || '-'"></strong>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-teal-200/80 shadow-2xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase tracking-wider">3. Kabupaten / Kota</span>
                            <strong class="text-slate-800 text-sm block mt-0.5" x-text="detectedGoogle?.kabupaten || '-'"></strong>
                        </div>
                    </div>

                    <div class="mt-3 text-[11px] text-slate-700 bg-white/60 p-2 rounded-lg border border-teal-100">
                        <span class="font-semibold text-teal-800">Alamat Lengkap Google:</span>
                        <span x-text="detectedGoogle?.alamat_lengkap"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="lat_input_edit" class="block text-xs font-semibold text-slate-700">
                            Latitude
                        </label>
                        <input type="number" step="0.0000001" id="lat_input_edit" name="latitude" value="{{ old('latitude', $ibuHamil->latitude) }}"
                               placeholder="Contoh: -6.917464"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                    </div>

                    <div>
                        <label for="lng_input_edit" class="block text-xs font-semibold text-slate-700">
                            Longitude
                        </label>
                        <input type="number" step="0.0000001" id="lng_input_edit" name="longitude" value="{{ old('longitude', $ibuHamil->longitude) }}"
                               placeholder="Contoh: 107.619123"
                               class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-mono" />
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('ibu-hamil.show', $ibuHamil) }}" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

