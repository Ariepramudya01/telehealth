@extends('layouts.app', ['title' => 'Input Skrining Deteksi Dini', 'header' => 'Formulir Skrining Deteksi Dini Risiko Kehamilan'])

@section('content')
<div x-data="skriningWizard(@js($ibuHamilsData), @js($questionsData), '{{ old('ibu_hamil_id', $selectedIbuHamilId) }}', @js(old('answers', [])), '{{ $selectedKunjunganId ?? '' }}', '{{ $syncedTanggal ?? '' }}', '{{ $selectedKunjungan?->tahap_anc ?? $selectedKunjungan?->jenis_kunjungan ?? '' }}')" class="space-y-6">

    <form action="{{ route('skrining.store') }}" method="POST" id="sprForm">
        @csrf
        <input type="hidden" name="kunjungan_id" :value="activeKunjunganId">

        <template x-if="activeKunjunganId && syncedKunjunganTanggal">
            <div class="mb-4 p-4 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-600 animate-pulse shrink-0"></span>
                    <div>
                        <span class="font-bold">🔗 Terkoneksi Otomatis dengan Kunjungan ANC:</span>
                        <span class="text-teal-800 ml-1">
                            Tanggal skrining otomatis diselaraskan dengan kunjungan
                            <strong x-text="syncedKunjunganTahap || 'Terbaru'"></strong> (<span x-text="formatDateId(syncedKunjunganTanggal)"></span>).
                            Menyelesaikan skrining ini akan otomatis menandai status kunjungan menjadi <strong>Dilayani</strong>.
                        </span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-teal-200/70 font-mono font-bold text-[11px] self-start sm:self-auto text-teal-950" x-text="'ID Kunjungan #' + activeKunjunganId"></span>
            </div>
        </template>

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
                                @if(isset($ibuHamilsSendiri) && $ibuHamilsSendiri->isNotEmpty())
                                    <optgroup label="Wilayah Sendiri (Desa {{ auth()->user()->desa?->nama ?? 'Wilayah Kerja' }})">
                                        @foreach($ibuHamilsSendiri as $bumil)
                                            <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                                {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - {{ $bumil->desa?->nama }} - Usia {{ $bumil->usia ?? '?' }} Th
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if(isset($ibuHamilsLintas) && $ibuHamilsLintas->isNotEmpty())
                                    <optgroup label="🌐 Lintas Daerah / Titik Kritis (Faskes & Desa Lain)">
                                        @foreach($ibuHamilsLintas as $bumil)
                                            <option value="{{ $bumil->id }}" {{ (old('ibu_hamil_id', $selectedIbuHamilId) == $bumil->id) ? 'selected' : '' }}>
                                                🌐 {{ $bumil->nama }} (NIK: {{ $bumil->nik ?? '-' }}) - Asal: {{ $bumil->desa?->nama }} ({{ $bumil->desa?->puskesmas ?? 'Puskesmas Luar' }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>

                            <!-- Cross District Banner in Skrining -->
                            <template x-if="selectedBumil && selectedBumil.is_lintas_daerah">
                                <div class="mt-2.5 p-3 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs flex items-start gap-2.5 shadow-2xs">
                                    <span class="text-base shrink-0">🌐</span>
                                    <div>
                                        <span class="font-bold block">Skrining Lintas Daerah / Titik Kritis</span>
                                        <span class="text-[11px] text-sky-700 block mt-0.5">
                                            Pasien berasal dari Desa <strong x-text="selectedBumil.desa_nama"></strong> (<span x-text="selectedBumil.puskesmas"></span>). Skrining Deteksi Dini SPR dilakukan oleh faskes <strong>{{ auth()->user()->desa?->nama ?? 'faskes Anda' }}</strong>.
                                        </span>
                                    </div>
                                </div>
                            </template>

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
                                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-[11px]">
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
                                            <strong :class="selectedBumil.is_obesitas ? 'text-amber-700 font-extrabold' : (selectedBumil.is_kek ? 'text-rose-600 font-extrabold' : 'text-slate-800')"
                                                    x-text="(selectedBumil.imt ? (selectedBumil.imt + ' (' + (selectedBumil.is_obesitas ? 'Obesitas' : (selectedBumil.kategori_imt || selectedBumil.status_gizi)) + ')') : '-')"></strong>
                                        </div>
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100">
                                            <span class="text-slate-500 block">Tensi & LiLA:</span>
                                            <strong :class="selectedBumil.is_hipertensi ? 'text-rose-600 font-extrabold' : 'text-slate-800'"
                                                    x-text="(selectedBumil.sistol ? (selectedBumil.sistol + '/' + selectedBumil.diastol + ' mmHg' + (selectedBumil.is_hipertensi ? (selectedBumil.is_hipertensi_berat ? ' [HT Berat]' : ' [Hipertensi]') : '')) : '-') + ' • LiLA ' + (selectedBumil.lila ? (selectedBumil.lila + ' cm') : '-')"></strong>
                                        </div>
                                        <div class="bg-white/90 p-2 rounded-xl border border-teal-100 col-span-2 sm:col-span-1">
                                            <span class="text-slate-500 block">Kadar Hb:</span>
                                            <strong :class="selectedBumil.hemoglobin && selectedBumil.hemoglobin < 11.0 ? 'text-rose-600 font-extrabold' : 'text-slate-800'"
                                                    x-text="selectedBumil.hemoglobin ? (selectedBumil.hemoglobin + ' g/dL ' + (selectedBumil.hemoglobin < 11.0 ? '(Anemia)' : '(Normal)')) : '-'"></strong>
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
                            <div class="relative mt-1.5">
                                <input type="text" id="tanggal_skrining" name="tanggal_skrining" required
                                       value="{{ old('tanggal_skrining', $syncedTanggal ?? date('Y-m-d')) }}"
                                       placeholder="HH-BB-TTTT (contoh: 18-10-2026)"
                                       class="datepicker-dmy block w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-xs sm:text-sm font-medium" />
                                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-teal-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <template x-if="syncedKunjunganTanggal">
                                <span class="text-[10px] text-emerald-700 font-bold mt-1 block">
                                    🔗 Otomatis disinkronkan dengan kunjungan ANC (<span x-text="syncedKunjunganTahap || 'Terbaru'"></span>: <span x-text="formatDateId(syncedKunjunganTanggal)"></span>)
                                </span>
                            </template>
                            <span class="text-[10px] text-teal-600 mt-1 block font-medium" x-show="!syncedKunjunganTanggal">Format: Hari-Bulan-Tahun (Contoh: {{ date('d-m-Y') }})</span>
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
    function skriningWizard(bumilsList = [], questions = [], initialBumilId = '', oldAnswers = {}, initialKunjunganId = '', initialSyncedTanggal = '', initialKunjunganTahap = '') {
        return {
            bumils: bumilsList || [],
            questions: questions || [],
            selectedIbuHamilId: initialBumilId || '',
            answers: {},
            locked: {},
            activeKunjunganId: initialKunjunganId || '',
            syncedKunjunganTanggal: initialSyncedTanggal || '',
            syncedKunjunganTahap: initialKunjunganTahap || '',
            selectedKunjunganTanggalApplied: false,

            formatDateId(isoStr) {
                if (!isoStr) return '';
                const parts = isoStr.split('-');
                if (parts.length === 3) return parts[2] + '-' + parts[1] + '-' + parts[0];
                return isoStr;
            },

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
                    this.applyBumilSync(false);
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
                this.applyBumilSync(true);
                this.evaluateLocks();
            },

            applyBumilSync(forceDateUpdate = false) {
                const bumil = this.selectedBumil;
                if (!bumil) return;

                if (bumil.latest_kunjungan_id) {
                    this.activeKunjunganId = bumil.latest_kunjungan_id;
                }
                if (bumil.latest_kunjungan_tahap) {
                    this.syncedKunjunganTahap = bumil.latest_kunjungan_tahap;
                }
                if (bumil.latest_kunjungan_tanggal) {
                    this.syncedKunjunganTanggal = bumil.latest_kunjungan_tanggal;
                    if (forceDateUpdate || !this.selectedKunjunganTanggalApplied) {
                        const tEl = document.getElementById('tanggal_skrining');
                        if (tEl) {
                            tEl.value = bumil.latest_kunjungan_tanggal;
                            if (tEl._flatpickr) {
                                tEl._flatpickr.setDate(bumil.latest_kunjungan_tanggal, true);
                            }
                        }
                        this.selectedKunjunganTanggalApplied = true;
                    }
                }
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

                // 5. Nomor 14: Penyakit ibu hamil (Anemia, KEK, IMT < 18.5, Obesitas IMT ≥ 27)
                if (byNomor[14]) {
                    const hbVal = parseFloat(bumil.hemoglobin);
                    const hasAnemia = Boolean(bumil.is_anemia || (!isNaN(hbVal) && hbVal > 0 && hbVal < 11.0));
                    const isUnderweight = Boolean(bumil.imt && bumil.imt < 18.5);
                    const hasKek = Boolean(bumil.is_kek || isUnderweight || bumil.status_gizi === 'Kurang Energi Kronis (KEK)');
                    const hasObesitas = Boolean(bumil.is_obesitas || (bumil.imt && bumil.imt >= 27.0) || bumil.status_gizi === 'obesitas');

                    if (hasAnemia || hasKek || hasObesitas) {
                        let reasons = [];
                        if (hasAnemia) {
                            const hbDisplay = (bumil.hemoglobin !== null && bumil.hemoglobin !== undefined && bumil.hemoglobin !== '') ? bumil.hemoglobin : hbVal;
                            reasons.push(`Anemia Kurang Darah (Kadar Hb ${hbDisplay} g/dL < 11.0 g/dL)`);
                        }
                        if (hasKek) {
                            let kekDetail = 'Risiko KEK';
                            if (bumil.lila) kekDetail += ` (LiLA ${bumil.lila} cm)`;
                            if (bumil.imt) kekDetail += ` (IMT ${bumil.imt})`;
                            reasons.push(kekDetail);
                        }
                        if (hasObesitas) {
                            let obesitasDetail = 'Obesitas';
                            if (bumil.imt) obesitasDetail += ` (IMT ${bumil.imt} kg/m² ≥ 27.0)`;
                            reasons.push(obesitasDetail);
                        }
                        this.setLock(byNomor[14].id, true, reasons.join(' & '));
                    }
                }

                // 6. Nomor 15: Hipertensi / Bengkak (Preeklampsia Ringan)
                if (byNomor[15]) {
                    const hasHipertensi = Boolean(bumil.is_hipertensi || bumil.sistol >= 140 || bumil.diastol >= 90);
                    const hasEdema = Boolean(bumil.edema === 'tungkai' || bumil.edema === 'seluruh_tubuh');
                    if (hasHipertensi || hasEdema) {
                        let reasons = [];
                        if (hasHipertensi) reasons.push(`Tekanan Darah Tinggi (${bumil.sistol}/${bumil.diastol} mmHg ≥ 140/90)`);
                        if (hasEdema) reasons.push(`Edema ${bumil.edema === 'tungkai' ? 'Tungkai' : 'Seluruh Tubuh'}`);
                        this.setLock(byNomor[15].id, true, reasons.join(' & '));
                    }
                }

                // 7. Nomor 23: Preeklampsia Berat / Hipertensi Berat (TD ≥ 160/110)
                if (byNomor[23]) {
                    const hasHipertensiBerat = Boolean(bumil.is_hipertensi_berat || bumil.sistol >= 160 || bumil.diastol >= 110);
                    if (hasHipertensiBerat) {
                        this.setLock(byNomor[23].id, true, `Preeklampsia Berat / Hipertensi Berat (${bumil.sistol}/${bumil.diastol} mmHg ≥ 160/110)`);
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

