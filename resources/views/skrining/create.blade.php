@extends('layouts.app', ['title' => 'Input Skrining Deteksi Dini', 'header' => 'Formulir Skrining Deteksi Dini Risiko Kehamilan'])

@section('content')
<div x-data="skriningWizard(@js($ibuHamilsData), @js($questionsData), '{{ old('ibu_hamil_id', $selectedIbuHamilId) }}', @js(old('answers', [])))" class="space-y-6">

    <form action="{{ route('skrining.store') }}" method="POST" id="sprForm">
        @csrf
        @if(!empty($selectedKunjunganId))
            <input type="hidden" name="kunjungan_id" value="{{ $selectedKunjunganId }}">
            <div class="mb-4 p-4 rounded-2xl bg-teal-50 border border-teal-200 text-teal-800 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teal-600 animate-pulse"></span>
                    <span><strong>Terkoneksi dengan Kunjungan ANC</strong>: Menyelesaikan skrining ini akan otomatis menandai status kunjungan menjadi <strong>Dilayani</strong>.</span>
                </div>
                <span class="px-2 py-0.5 rounded bg-teal-200/60 font-mono font-bold text-[11px]">ID #{{ $selectedKunjunganId }}</span>
            </div>
        @endif

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
                                    x-model="selectedIbuHamilId"
                                    @change="onIbuHamilChange()"
                                    class="mt-1.5 block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm">
                                <option value="">-- Pilih Nama Ibu Hamil --</option>
                                @foreach($ibuHamils as $bumil)
                                    <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                        {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - {{ $bumil->desa?->nama }} - Usia {{ $bumil->usia ?? '?' }} Th
                                    </option>
                                @endforeach
                            </select>

                            <!-- Mini Profil Biometrik & Fisik Bumil Terpilih -->
                            <template x-if="selectedBumil">
                                <div class="mt-3 p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200 text-xs">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="font-bold text-teal-900 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Profil Fisik & Gizi: <span x-text="selectedBumil.nama"></span>
                                        </span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white border border-teal-300 text-teal-800"
                                              x-text="'Gravida ke-' + (selectedBumil.gravida || 1)"></span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100">
                                            <span class="text-slate-500 block">Usia:</span>
                                            <strong class="text-slate-800" x-text="selectedBumil.usia !== null ? (selectedBumil.usia + ' Tahun') : '-'"></strong>
                                        </div>
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100">
                                            <span class="text-slate-500 block">TB & BB:</span>
                                            <strong class="text-slate-800" x-text="(selectedBumil.tinggi_badan || '-') + ' cm / ' + (selectedBumil.berat_badan || '-') + ' kg'"></strong>
                                        </div>
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100">
                                            <span class="text-slate-500 block">IMT & Gizi:</span>
                                            <strong class="text-slate-800" x-text="(selectedBumil.imt ? (selectedBumil.imt + ' (' + (selectedBumil.kategori_imt || selectedBumil.status_gizi) + ')') : '-')"></strong>
                                        </div>
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100">
                                            <span class="text-slate-500 block">Tensi & LiLA:</span>
                                            <strong class="text-slate-800" x-text="(selectedBumil.sistol ? (selectedBumil.sistol + '/' + selectedBumil.diastol + ' mmHg') : '-') + ' • LiLA ' + (selectedBumil.lila ? (selectedBumil.lila + ' cm') : '-')"></strong>
                                        </div>
                                    </div>
                                    <template x-if="hasActiveLocks">
                                        <div class="mt-2.5 pt-2 border-t border-teal-200/70 text-[11px] text-teal-900 font-semibold flex items-center gap-1.5">
                                            <span>🔒 Pertanyaan skrining yang berkaitan dengan data biometrik/fisik di atas telah dikunci secara otomatis.</span>
                                        </div>
                                    </template>
                                </div>
                            </template>
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
                            <p class="text-[11px] text-emerald-700">Secara standar obstetri klinis, setiap wanita hamil dimulai dengan skor dasar 2</p>
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
                            <label class="flex items-start gap-3 p-3 rounded-xl border transition-all"
                                   :class="[
                                       answers[{{ $p->id }}] ? 'bg-teal-50/40 border-teal-400 ring-1 ring-teal-400/20' : 'border-slate-100 hover:border-teal-300 hover:bg-teal-50/20',
                                       isLocked({{ $p->id }}) ? 'cursor-not-allowed bg-slate-50/80 border-slate-200' : 'cursor-pointer'
                                   ]">
                                <input type="checkbox"
                                       name="answers[{{ $p->id }}]"
                                       value="1"
                                       x-model="answers[{{ $p->id }}]"
                                       :disabled="isLocked({{ $p->id }})"
                                       class="mt-1 h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded disabled:opacity-60"
                                       :class="isLocked({{ $p->id }}) ? 'cursor-not-allowed' : 'cursor-pointer'" />

                                <template x-if="isLocked({{ $p->id }}) && answers[{{ $p->id }}]">
                                    <input type="hidden" name="answers[{{ $p->id }}]" value="1" />
                                </template>

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

                                    <template x-if="isLocked({{ $p->id }})">
                                        <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>🔒 Terkunci Otomatis (Berisiko):</span>
                                            <span class="font-normal" x-text="getLockReason({{ $p->id }})"></span>
                                        </div>
                                    </template>
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
                            <label class="flex items-start gap-3 p-3 rounded-xl border transition-all"
                                   :class="[
                                       answers[{{ $p->id }}] ? 'bg-amber-50/40 border-amber-400 ring-1 ring-amber-400/20' : 'border-slate-100 hover:border-amber-300 hover:bg-amber-50/20',
                                       isLocked({{ $p->id }}) ? 'cursor-not-allowed bg-slate-50/80 border-slate-200' : 'cursor-pointer'
                                   ]">
                                <input type="checkbox"
                                       name="answers[{{ $p->id }}]"
                                       value="1"
                                       x-model="answers[{{ $p->id }}]"
                                       :disabled="isLocked({{ $p->id }})"
                                       class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-slate-300 rounded disabled:opacity-60"
                                       :class="isLocked({{ $p->id }}) ? 'cursor-not-allowed' : 'cursor-pointer'" />

                                <template x-if="isLocked({{ $p->id }}) && answers[{{ $p->id }}]">
                                    <input type="hidden" name="answers[{{ $p->id }}]" value="1" />
                                </template>

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

                                    <template x-if="isLocked({{ $p->id }})">
                                        <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>🔒 Terkunci Otomatis (Berisiko):</span>
                                            <span class="font-normal" x-text="getLockReason({{ $p->id }})"></span>
                                        </div>
                                    </template>
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
                            <label class="flex items-start gap-3 p-3.5 rounded-xl border transition-all"
                                   :class="[
                                       answers[{{ $p->id }}] ? 'bg-rose-50/40 border-rose-400 ring-1 ring-rose-400/20' : 'border-slate-100 hover:border-rose-300 hover:bg-rose-50/20',
                                       isLocked({{ $p->id }}) ? 'cursor-not-allowed bg-slate-50/80 border-slate-200' : 'cursor-pointer'
                                   ]">
                                <input type="checkbox"
                                       name="answers[{{ $p->id }}]"
                                       value="1"
                                       x-model="answers[{{ $p->id }}]"
                                       :disabled="isLocked({{ $p->id }})"
                                       class="mt-1 h-4 w-4 text-rose-600 focus:ring-rose-500 border-slate-300 rounded disabled:opacity-60"
                                       :class="isLocked({{ $p->id }}) ? 'cursor-not-allowed' : 'cursor-pointer'" />

                                <template x-if="isLocked({{ $p->id }}) && answers[{{ $p->id }}]">
                                    <input type="hidden" name="answers[{{ $p->id }}]" value="1" />
                                </template>

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

                                    <template x-if="isLocked({{ $p->id }})">
                                        <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>🔒 Terkunci Otomatis (Berisiko):</span>
                                            <span class="font-normal" x-text="getLockReason({{ $p->id }})"></span>
                                        </div>
                                    </template>
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
    function skriningWizard(bumilsList = [], questions = [], initialBumilId = '', oldAnswers = {}) {
        return {
            bumils: bumilsList || [],
            questions: questions || [],
            selectedIbuHamilId: initialBumilId || '',
            answers: {},
            locked: {},

            get selectedBumil() {
                if (!this.selectedIbuHamilId) return null;
                return this.bumils.find(b => String(b.id) === String(this.selectedIbuHamilId)) || null;
            },

            get hasActiveLocks() {
                return Object.values(this.locked).some(l => l && l.isLocked && l.value);
            },

            get additionalScore() {
                let sum = 0;
                this.questions.forEach(q => {
                    if (this.answers[q.id]) {
                        sum += q.skor;
                    }
                });
                return sum;
            },

            get totalScore() {
                return 2 + this.additionalScore;
            },

            init() {
                if (oldAnswers && typeof oldAnswers === 'object') {
                    Object.keys(oldAnswers).forEach(key => {
                        if (oldAnswers[key] == '1' || oldAnswers[key] === true) {
                            this.answers[key] = true;
                        }
                    });
                }
                if (this.selectedIbuHamilId) {
                    this.evaluateLocks();
                }
            },

            isLocked(id) {
                return Boolean(this.locked && this.locked[id] && this.locked[id].isLocked);
            },

            getLockReason(id) {
                return (this.locked && this.locked[id] && this.locked[id].reason) || '';
            },

            onIbuHamilChange() {
                // Hapus jawaban yang sebelumnya terkunci otomatis
                Object.keys(this.locked).forEach(id => {
                    if (this.locked[id] && this.locked[id].isLocked) {
                        delete this.answers[id];
                    }
                });
                this.locked = {};
                this.evaluateLocks();
            },

            evaluateLocks() {
                const bumil = this.selectedBumil;
                this.locked = {};

                if (!bumil) return;

                // Map questions by nomor
                const byNomor = {};
                this.questions.forEach(q => {
                    byNomor[q.nomor] = q;
                });

                // 1. Nomor 1: Terlalu muda (≤ 16 th)
                if (byNomor[1] && bumil.usia !== null && bumil.usia !== undefined && bumil.usia <= 16) {
                    this.setLock(byNomor[1].id, true, `Usia ibu ${bumil.usia} tahun (≤ 16 th)`);
                }

                // 2. Nomor 2: Terlalu tua hamil I (≥ 35 th & Gravida 1)
                if (byNomor[2] && bumil.usia !== null && bumil.usia !== undefined && bumil.usia >= 35 && (bumil.gravida == 1 || !bumil.gravida)) {
                    this.setLock(byNomor[2].id, true, `Usia ibu ${bumil.usia} th & Hamil Pertama (Gravida 1)`);
                }

                // 3. Nomor 7: Terlalu tua umur (≥ 35 th & Gravida > 1)
                if (byNomor[7] && bumil.usia !== null && bumil.usia !== undefined && bumil.usia >= 35 && bumil.gravida > 1) {
                    this.setLock(byNomor[7].id, true, `Usia ibu ${bumil.usia} th & Pernah Hamil (Gravida ${bumil.gravida})`);
                }

                // 4. Nomor 8: Terlalu pendek (≤ 145 cm)
                if (byNomor[8] && bumil.tinggi_badan !== null && bumil.tinggi_badan > 0 && bumil.tinggi_badan <= 145) {
                    this.setLock(byNomor[8].id, true, `Tinggi badan ${bumil.tinggi_badan} cm (≤ 145 cm)`);
                }

                // 5. Nomor 14: Penyakit ibu hamil (Anemia, KEK, IMT < 18.5)
                if (byNomor[14]) {
                    const hasAnemia = Boolean(bumil.is_anemia);
                    const isUnderweight = Boolean(bumil.imt && bumil.imt < 18.5);
                    const hasKek = Boolean(bumil.is_kek || isUnderweight || bumil.status_gizi === 'Kurang Energi Kronis (KEK)');
                    if (hasAnemia || hasKek) {
                        let reasons = [];
                        if (hasAnemia) reasons.push(`Anemia (Hb ${bumil.hemoglobin} g/dL)`);
                        if (hasKek) {
                            let kekDetail = 'Risiko KEK';
                            if (bumil.lila) kekDetail += ` (LiLA ${bumil.lila} cm)`;
                            if (bumil.imt) kekDetail += ` (IMT ${bumil.imt})`;
                            reasons.push(kekDetail);
                        }
                        this.setLock(byNomor[14].id, true, reasons.join(' & '));
                    }
                }

                // 6. Nomor 15: Hipertensi / Bengkak (Preeklampsia)
                if (byNomor[15]) {
                    const hasHipertensi = Boolean(bumil.sistol >= 140 || bumil.diastol >= 90);
                    const hasEdema = Boolean(bumil.edema === 'tungkai' || bumil.edema === 'seluruh_tubuh');
                    if (hasHipertensi || hasEdema) {
                        let reasons = [];
                        if (hasHipertensi) reasons.push(`Tekanan Darah Tinggi (${bumil.sistol}/${bumil.diastol} mmHg)`);
                        if (hasEdema) reasons.push(`Edema ${bumil.edema === 'tungkai' ? 'Tungkai' : 'Seluruh Tubuh'}`);
                        this.setLock(byNomor[15].id, true, reasons.join(' & '));
                    }
                }
            },

            setLock(questionId, value, reason) {
                this.locked[questionId] = {
                    isLocked: true,
                    value: !!value,
                    reason: reason
                };
                this.answers[questionId] = !!value;
            }
        };
    }
</script>
@endpush

