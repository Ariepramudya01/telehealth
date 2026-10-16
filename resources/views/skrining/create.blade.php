@extends('layouts.app', ['title' => 'Input Skrining Skor Poedji Rochjati', 'header' => 'Formulir Skrining Skor Poedji Rochjati (SPR)'])

@section('content')
<div x-data="skriningWizard()" class="space-y-6">

    <form action="{{ route('skrining.store') }}" method="POST" id="sprForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left / Main: The Questionnaire -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Informasi Dasar Skrining -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800">1. Data Pemeriksaan Ibu Hamil</h3>
                        <p class="text-xs text-slate-500">Pilih ibu hamil dan masukkan waktu pemeriksaan skrining</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="ibu_hamil_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Pilih Ibu Hamil <span class="text-rose-500">*</span>
                            </label>
                            <select id="ibu_hamil_id" name="ibu_hamil_id" required
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="">-- Pilih Nama Ibu Hamil --</option>
                                @foreach($ibuHamils as $bumil)
                                    <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                        {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - {{ $bumil->desa?->nama }} - Usia {{ $bumil->usia ?? '?' }} Th
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="tanggal_skrining" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Tanggal Skrining <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" id="tanggal_skrining" name="tanggal_skrining" required
                                   value="{{ old('tanggal_skrining', date('Y-m-d')) }}"
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        </div>

                        <div>
                            <label for="usia_kehamilan_minggu" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Usia Kehamilan Saat Ini (Minggu)
                            </label>
                            <input type="number" min="1" max="45" id="usia_kehamilan_minggu" name="usia_kehamilan_minggu"
                                   value="{{ old('usia_kehamilan_minggu') }}"
                                   placeholder="Contoh: 24"
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        </div>

                        <div>
                            <label for="trimester" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Trimester Kehamilan
                            </label>
                            <select id="trimester" name="trimester"
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="">-- Pilih Trimester --</option>
                                <option value="1" {{ old('trimester') == '1' ? 'selected' : '' }}>Trimester I (1 - 12 Minggu)</option>
                                <option value="2" {{ old('trimester') == '2' ? 'selected' : '' }}>Trimester II (13 - 27 Minggu)</option>
                                <option value="3" {{ old('trimester') == '3' ? 'selected' : '' }}>Trimester III (28 - 40+ Minggu)</option>
                            </select>
                        </div>

                        <div>
                            <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Catatan Pemeriksa Tambahan
                            </label>
                            <input type="text" id="catatan" name="catatan" value="{{ old('catatan') }}"
                                   placeholder="Keluhan ibu atau saran khusus..."
                                   class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm" />
                        </div>
                    </div>
                </div>

                <!-- Skor Dasar (Otomatis 2) -->
                <div class="bg-emerald-50/80 p-4 rounded-2xl border border-emerald-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-extrabold flex items-center justify-center text-xs">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-emerald-900">Skor Awal Dasar Setiap Ibu Hamil</h4>
                            <p class="text-[11px] text-emerald-700">Sesuai standar Poedji Rochjati, setiap wanita hamil dimulai dengan skor dasar 2</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-white border border-emerald-300 font-extrabold text-emerald-800 text-sm">
                        +2
                    </span>
                </div>

                <!-- KELOMPOK I: Ada Potensi Gawat Obstetri (APGO) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="border-b border-slate-100 pb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800 uppercase tracking-wider">
                                Kelompok I • APGO
                            </span>
                            <span class="text-xs text-slate-400 font-medium">13 Pertanyaan (@ Skor 4)</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mt-1">Ada Potensi Gawat Obstetri (7 Terlalu & 3 Pernah)</h3>
                        <p class="text-xs text-slate-500">Centang tanda centang (✓) jika kondisi di bawah ini dialami atau ada pada ibu hamil</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @foreach($kelompokI as $p)
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:border-teal-300 hover:bg-teal-50/20 cursor-pointer transition-all"
                                   :class="selectedQuestions.includes({{ $p->id }}) ? 'bg-teal-50/40 border-teal-400 ring-1 ring-teal-400/20' : ''">
                                <input type="checkbox" name="answers[{{ $p->id }}]" value="1"
                                       @change="toggleQuestion({{ $p->id }}, {{ $p->skor }})"
                                       class="mt-1 h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-800">
                                            #{{ $p->nomor }}. {{ $p->pertanyaan }}
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 shrink-0">
                                            +{{ $p->skor }}
                                        </span>
                                    </div>
                                    @if($p->keterangan)
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $p->keterangan }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- KELOMPOK II: Ada Gawat Obstetri (AGO) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="border-b border-slate-100 pb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 uppercase tracking-wider">
                                Kelompok II • AGO
                            </span>
                            <span class="text-xs text-slate-400 font-medium">8 Pertanyaan (Skor 4 & 8)</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mt-1">Ada Gawat Obstetri (Tanda Bahaya Kehamilan)</h3>
                        <p class="text-xs text-slate-500">Penyakit penyerta, kelainan letak, kehamilan kembar atau kelainan janin</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @foreach($kelompokII as $p)
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:border-amber-300 hover:bg-amber-50/20 cursor-pointer transition-all"
                                   :class="selectedQuestions.includes({{ $p->id }}) ? 'bg-amber-50/40 border-amber-400 ring-1 ring-amber-400/20' : ''">
                                <input type="checkbox" name="answers[{{ $p->id }}]" value="1"
                                       @change="toggleQuestion({{ $p->id }}, {{ $p->skor }})"
                                       class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-slate-300 rounded" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-800">
                                            #{{ $p->nomor }}. {{ $p->pertanyaan }}
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded {{ $p->skor >= 8 ? 'bg-rose-100 text-rose-700' : 'bg-amber-50 text-amber-700' }} shrink-0">
                                            +{{ $p->skor }}
                                        </span>
                                    </div>
                                    @if($p->keterangan)
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $p->keterangan }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- KELOMPOK III: Ada Gawat Darurat Obstetri (AGDO) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-3">
                    <div class="border-b border-slate-100 pb-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800 uppercase tracking-wider">
                                Kelompok III • AGDO
                            </span>
                            <span class="text-xs text-rose-600 font-bold">2 Pertanyaan (@ Skor 8)</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-800 mt-1">Ada Gawat Darurat Obstetri (Kondisi Kritis)</h3>
                        <p class="text-xs text-slate-500">Perdarahan aktif atau preeklampsia berat/kejang (Rujukan darurat langsung!)</p>
                    </div>

                    <div class="space-y-2 pt-1">
                        @foreach($kelompokIII as $p)
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-100 hover:border-rose-300 hover:bg-rose-50/20 cursor-pointer transition-all"
                                   :class="selectedQuestions.includes({{ $p->id }}) ? 'bg-rose-50/40 border-rose-400 ring-1 ring-rose-400/20' : ''">
                                <input type="checkbox" name="answers[{{ $p->id }}]" value="1"
                                       @change="toggleQuestion({{ $p->id }}, {{ $p->skor }})"
                                       class="mt-1 h-4 w-4 text-rose-600 focus:ring-rose-500 border-slate-300 rounded" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-rose-900">
                                            #{{ $p->nomor }}. {{ $p->pertanyaan }}
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800 shrink-0">
                                            +{{ $p->skor }}
                                        </span>
                                    </div>
                                    @if($p->keterangan)
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $p->keterangan }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right / Sticky: Live Score & Risk Summary -->
            <div class="space-y-6">
                <div class="sticky top-24 space-y-4">

                    <!-- Live Score Card -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-md">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Kalkulator Skor Langsung</span>
                        
                        <div class="mt-4 flex items-baseline justify-between border-b border-slate-100 pb-4">
                            <div>
                                <span class="text-4xl font-extrabold text-slate-900" x-text="totalScore">2</span>
                                <span class="text-xs text-slate-500 ml-1">Total Poin</span>
                            </div>
                            <div class="text-right text-xs">
                                <span class="text-slate-400 block">Skor Dasar: 2</span>
                                <span class="text-teal-700 font-semibold block">+<span x-text="additionalScore">0</span> Tambahan</span>
                            </div>
                        </div>

                        <!-- Live Category Badge -->
                        <div class="mt-4">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Klasifikasi Risiko:</span>
                            
                            <template x-if="totalScore == 2">
                                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                        <h4 class="font-extrabold text-sm">KRR (Risiko Rendah)</h4>
                                    </div>
                                    <p class="text-xs text-emerald-700 mt-1 leading-relaxed">
                                        Kehamilan normal tanpa komplikasi. Tempat persalinan: Rumah, Polindes, atau Puskesmas oleh Bidan.
                                    </p>
                                </div>
                            </template>

                            <template x-if="totalScore >= 6 && totalScore <= 10">
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                        <h4 class="font-extrabold text-sm">KRT (Risiko Tinggi)</h4>
                                    </div>
                                    <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                        Kehamilan dengan satu atau lebih faktor bahaya obstetri. Tempat persalinan: Puskesmas PONED atau Rumah Sakit oleh Bidan & Dokter.
                                    </p>
                                </div>
                            </template>

                            <template x-if="totalScore >= 12">
                                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 animate-pulse-subtle">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-rose-500 animate-ping"></span>
                                        <h4 class="font-extrabold text-sm">KRST (Risiko Sangat Tinggi)</h4>
                                    </div>
                                    <p class="text-xs text-rose-800 mt-1 leading-relaxed font-medium">
                                        ⚠️ Kondisi kritis atau gawat darurat obstetri! Rujukan segera ke Rumah Sakit. Penolong persalinan: Dokter Spesialis Kebidanan (SpOG).
                                    </p>
                                </div>
                            </template>
                        </div>

                        <!-- Guide Legend -->
                        <div class="mt-4 pt-4 border-t border-slate-100 text-[11px] space-y-1.5 text-slate-500">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Skor 2:</span>
                                <span class="font-semibold text-emerald-700">KRR (Risiko Rendah)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Skor 6 - 10:</span>
                                <span class="font-semibold text-amber-700">KRT (Risiko Tinggi)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Skor ≥ 12:</span>
                                <span class="font-semibold text-rose-700">KRST (Risiko Sangat Tinggi)</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6">
                            <button type="submit" 
                                    class="w-full py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Hasil Skrining</span>
                            </button>
                        </div>
                    </div>

                    <!-- Petunjuk Kader -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 text-xs text-slate-600">
                        <strong class="font-semibold text-slate-700 block mb-1">💡 Tips Skrining Kader:</strong>
                        Tanyakan setiap pertanyaan secara ramah dan teliti. Jika ibu belum paham, jelaskan bahasa medis dengan istilah lokal (misal: "uri dirogoh", "tarikan vakum").
                    </div>

                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    function skriningWizard() {
        return {
            selectedQuestions: [],
            additionalScore: 0,
            get totalScore() {
                return 2 + this.additionalScore;
            },
            toggleQuestion(id, skor) {
                const index = this.selectedQuestions.indexOf(id);
                if (index > -1) {
                    this.selectedQuestions.splice(index, 1);
                    this.additionalScore -= skor;
                } else {
                    this.selectedQuestions.push(id);
                    this.additionalScore += skor;
                }
            }
        };
    }
</script>
@endpush

