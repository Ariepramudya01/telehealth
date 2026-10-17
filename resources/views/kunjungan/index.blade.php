@extends('layouts.app', ['title' => 'Catatan Kunjungan ANC', 'header' => 'Pencatatan Kunjungan ANC (Hadir & Dilayani)'])

@section('content')
    <div class="space-y-6" x-data="{
        modalOpen: false,
        activeKunjunganId: null,
        activeNama: '',
        activeJenis: '',
        activeTanggal: '',
        activeCatatan: '',
        activeHadir: true,
        activeDilayani: true,
        activeTb: '',
        activeBb: '',
        activeSistol: '',
        activeDiastol: '',
        activeHasSkrining: false,
        activeBumilId: null,
        openConfirmModal(id, nama, jenis, tanggal, catatan = '', hadir = true, dilayani = true, tb = '', bb = '', sistol = '', diastol = '', hasSkrining = false, bumilId = null) {
            this.activeKunjunganId = id;
            this.activeNama = nama;
            this.activeJenis = jenis;
            this.activeTanggal = tanggal;
            this.activeCatatan = catatan;
            this.activeHadir = Boolean(hadir);
            this.activeDilayani = Boolean(dilayani);
            this.activeTb = tb;
            this.activeBb = bb;
            this.activeSistol = sistol;
            this.activeDiastol = diastol;
            this.activeHasSkrining = Boolean(hasSkrining);
            this.activeBumilId = bumilId;
            this.modalOpen = true;
        }
    }">

        <!-- Header Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Kunjungan Antenatal Care (ANC)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status kehadiran dan pelayanan ibu hamil (K1 s/d K6) di
                    faskes</p>
            </div>

            @if (!auth()->user()->isKader())
                <a href="{{ route('kunjungan.create') }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Catat Kunjungan Baru</span>
                </a>
            @endif
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('kunjungan.index') }}"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @if (auth()->user()->isAdmin())
                    <div>
                        <select name="desa_id" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Wilayah Desa --</option>
                            @foreach ($desas as $d)
                                <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <select name="jenis" onchange="this.form.submit()"
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Jenis Kunjungan (K1 - K6) --</option>
                        @foreach (['K1', 'K2', 'K3', 'K4', 'K5', 'K6'] as $k)
                            <option value="{{ $k }}" {{ request('jenis') === $k ? 'selected' : '' }}>Kunjungan
                                {{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Status --</option>
                        <option value="belum_hadir" {{ request('status') === 'belum_hadir' ? 'selected' : '' }}>Belum Hadir
                            / Terjadwal</option>
                        <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="dilayani" {{ request('status') === 'dilayani' ? 'selected' : '' }}>Dilayani Lengkap
                        </option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs">
                        Terapkan
                    </button>
                    @if (request()->hasAny(['desa_id', 'jenis', 'status']))
                        <a href="{{ route('kunjungan.index') }}"
                            class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Kunjungan Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal Jadwal</th>
                            <th class="py-3 px-4">Nama Ibu Hamil</th>
                            <th class="py-3 px-4">Wilayah Desa</th>
                            <th class="py-3 px-4 text-center">Jenis ANC</th>
                            <th class="py-3 px-4">Pemeriksaan Fisik</th>
                            <th class="py-3 px-4 text-center">Status Kehadiran</th>
                            <th class="py-3 px-4 text-center">Status Pelayanan</th>
                            <th class="py-3 px-4">Catatan</th>
                            <th class="py-3 px-4 text-right">Aksi Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kunjungans as $k)
                            @php
                                $hasSkriningKunjungan = $k->skrining_id !== null || $k->ibuHamil->skrinings()->exists();
                                $tbVal = $k->tinggi_badan ?? ($k->ibuHamil->tinggi_badan ?? '');
                                $bbVal = $k->berat_badan ?? ($k->ibuHamil->berat_badan ?? '');
                                $sistolVal = $k->tekanan_darah_sistol ?? ($k->ibuHamil->tekanan_darah_sistol ?? '');
                                $diastolVal = $k->tekanan_darah_diastol ?? ($k->ibuHamil->tekanan_darah_diastol ?? '');
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $k->tanggal_kunjungan->translatedFormat('d F Y') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('ibu-hamil.show', $k->ibuHamil) }}"
                                        class="font-bold text-slate-800 hover:text-teal-700">
                                        {{ $k->ibuHamil->nama }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    {{ $k->desa?->nama }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold bg-slate-100 text-slate-800">
                                        {{ $k->jenis_kunjungan }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <div class="flex flex-col gap-0.5 text-[11px]">
                                        <span><strong class="text-slate-800">TB / BB:</strong>
                                            {{ $tbVal ? $tbVal . ' cm' : '-' }} /
                                            {{ $bbVal ? $bbVal . ' kg' : '-' }}</span>
                                        <span><strong class="text-slate-800">TD:</strong>
                                            {{ $sistolVal && $diastolVal ? "{$sistolVal}/{$diastolVal} mmHg" : '-' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_hadir)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            ✓ Hadir
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            ⏳ Belum Hadir
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($k->status_dilayani)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ✓ Dilayani
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                            Belum Dilayani
                                        </span>
                                        @if (!$hasSkriningKunjungan)
                                            <a href="{{ route('skrining.create', ['ibu_hamil_id' => $k->ibu_hamil_id, 'kunjungan_id' => $k->id]) }}"
                                                class="block text-[10px] text-teal-700 font-bold hover:underline mt-1">
                                                + Isi Skrining SPR
                                            </a>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate" title="{{ $k->catatan }}">
                                    {{ $k->catatan ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (!$k->status_hadir)
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $hasSkriningKunjungan ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                                <span>Konfirmasi Hadir</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                @click="openConfirmModal({{ $k->id }}, '{{ addslashes($k->ibuHamil->nama) }}', '{{ $k->jenis_kunjungan }}', '{{ $k->tanggal_kunjungan->format('Y-m-d') }}', '{{ addslashes($k->catatan ?? '') }}', true, {{ $k->status_dilayani ? 'true' : 'false' }}, '{{ $tbVal }}', '{{ $bbVal }}', '{{ $sistolVal }}', '{{ $diastolVal }}', {{ $hasSkriningKunjungan ? 'true' : 'false' }}, {{ $k->ibu_hamil_id }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs border border-slate-200 transition-all">
                                                <span>Ubah Status</span>
                                            </button>
                                        @endif

                                        @if (!auth()->user()->isKader())
                                            <a href="{{ route('kunjungan.edit', $k) }}"
                                                class="text-blue-600 hover:text-blue-700 font-semibold text-xs ml-1">
                                                Edit
                                            </a>
                                            <form action="{{ route('kunjungan.destroy', $k) }}" method="POST"
                                                onsubmit="return confirm('Hapus catatan kunjungan ini?');"
                                                class="inline ml-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 text-xs">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    Belum ada data catatan kunjungan ANC.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($kunjungans->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $kunjungans->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Konfirmasi Kehadiran -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="modalOpen = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition:enter="ease-out duration-300"
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
                                <span
                                    class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white uppercase tracking-wider"
                                    x-text="'ANC ' + activeJenis"></span>
                                <button type="button" @click="modalOpen = false"
                                    class="text-white/80 hover:text-white text-base">✕</button>
                            </div>
                            <h3 class="text-base font-extrabold mt-1">Konfirmasi Kehadiran & Pelayanan ANC</h3>
                            <p class="text-xs text-teal-100 mt-0.5">Ibu Hamil: <strong class="text-white font-bold"
                                    x-text="activeNama"></strong></p>
                        </div>

                        <div class="p-6 space-y-4 text-xs">
                            <!-- Tanggal Kehadiran Realisasi -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Tanggal Kunjungan Aktual <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal_kunjungan" x-model="activeTanggal" required
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500" />
                            </div>

                            <!-- Pemeriksaan Fisik: TB, BB, TD -->
                            <div class="p-3.5 rounded-2xl bg-teal-50/60 border border-teal-200/80 space-y-2.5">
                                <span class="text-[11px] font-bold text-teal-900 block">Pemeriksaan Fisik Kunjungan:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TB
                                            (cm)</label>
                                        <input type="number" step="0.1" name="tinggi_badan" x-model="activeTb"
                                            placeholder="158"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">BB
                                            (kg)</label>
                                        <input type="number" step="0.1" name="berat_badan" x-model="activeBb"
                                            placeholder="56"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                            Sistol</label>
                                        <input type="number" name="tekanan_darah_sistol" x-model="activeSistol"
                                            placeholder="120"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 text-[10px] uppercase mb-0.5">TD
                                            Diastol</label>
                                        <input type="number" name="tekanan_darah_diastol" x-model="activeDiastol"
                                            placeholder="80"
                                            class="w-full rounded-xl border border-slate-300 px-2.5 py-1.5 text-xs text-slate-900 bg-white focus:ring-2 focus:ring-teal-500" />
                                    </div>
                                </div>
                            </div>

                            <!-- Status Checkboxes & Skrining SPR Link -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="hidden" name="status_hadir" value="0" />
                                    <input type="checkbox" name="status_hadir" value="1" x-model="activeHadir"
                                        class="mt-0.5 h-4 w-4 rounded text-teal-600 focus:ring-teal-500 border-slate-300 cursor-pointer" />
                                    <div>
                                        <strong class="text-slate-800 block text-xs">Ibu Hamil Hadir di Faskes /
                                            Posyandu</strong>
                                        <span class="text-[11px] text-slate-500">Tandai jika ibu hamil datang pada jadwal
                                            kunjungan ini</span>
                                    </div>
                                </label>

                                <div class="pt-2.5 border-t border-slate-200">
                                    <template x-if="activeHasSkrining">
                                        <label class="flex items-start gap-3 cursor-pointer"
                                            :class="!activeHadir ? 'opacity-50 pointer-events-none' : ''">
                                            <input type="hidden" name="status_dilayani" value="0" />
                                            <input type="checkbox" name="status_dilayani" value="1"
                                                x-model="activeDilayani" :disabled="!activeHadir"
                                                class="mt-0.5 h-4 w-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer" />
                                            <div>
                                                <strong class="text-slate-800 block text-xs">Pelayanan Medis Lengkap (Telah
                                                    Dilayani)</strong>
                                                <span class="text-[11px] text-emerald-700 font-medium">✓ Skrining SPR
                                                    Terpenuhi</span>
                                            </div>
                                        </label>
                                    </template>
                                    <template x-if="!activeHasSkrining">
                                        <div
                                            class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex flex-col gap-2">
                                            <div>
                                                <strong class="block">⚠️ Skrining SPR Belum Diisi</strong>
                                                <span class="text-[11px] text-amber-700">Status Dilayani hanya dapat
                                                    dicatat setelah ibu hamil mengisi kuesioner Skrining SPR.</span>
                                            </div>
                                            <div>
                                                <a :href="'{{ url('/skrining/create') }}?ibu_hamil_id=' + activeBumilId +
                                                    '&kunjungan_id=' + activeKunjunganId"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs transition-all">
                                                    <span>+ Isi Skrining SPR Sekarang (Otomatis Dilayani) →</span>
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label
                                    class="block font-semibold text-slate-700 uppercase tracking-wider text-[11px] mb-1">
                                    Catatan Pemeriksaan / Hasil Pelayanan (Opsional)
                                </label>
                                <textarea name="catatan" x-model="activeCatatan" rows="2"
                                    placeholder="Contoh: Tekanan darah normal, telah diberikan tablet Fe..."
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-900 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 bg-slate-100 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Konfirmasi</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
@endsection
