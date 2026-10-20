@extends('layouts.app', ['title' => 'Wilayah Kerja Binaan', 'header' => 'Wilayah Kerja Binaan'])

@section('content')
<div class="space-y-6">

    <!-- Filter & Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Wilayah Kerja Binaan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Wilayah kerja pelayanan kesehatan ibu hamil, posyandu, dan pemantauan deteksi dini risiko ANC</p>
        </div>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('desa.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Wilayah Binaan</span>
            </a>
        @endif
    </div>

    <!-- Filter by Puskesmas / Kecamatan -->
    @if(auth()->user()->isAdmin() && ($puskesmasList->isNotEmpty() || $kecamatanList->isNotEmpty()))
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form action="{{ route('desa.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Wilayah:
                </span>

                @if($puskesmasList->isNotEmpty())
                    <div>
                        <select name="puskesmas" onchange="this.form.submit()" 
                                class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-700 focus:ring-teal-500 focus:border-teal-500">
                            <option value="">-- Semua Puskesmas / Instansi --</option>
                            @foreach($puskesmasList as $pkm)
                                <option value="{{ $pkm }}" {{ $selectedPuskesmas === $pkm ? 'selected' : '' }}>
                                    {{ $pkm }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($kecamatanList->isNotEmpty())
                    <div>
                        <select name="kecamatan" onchange="this.form.submit()" 
                                class="rounded-xl border border-slate-300 py-1.5 px-3 text-xs text-slate-700 focus:ring-teal-500 focus:border-teal-500">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach($kecamatanList as $kec)
                                <option value="{{ $kec }}" {{ $selectedKecamatan === $kec ? 'selected' : '' }}>
                                    {{ $kec }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($selectedPuskesmas || $selectedKecamatan)
                    <a href="{{ route('desa.index') }}" class="text-xs text-teal-700 font-semibold hover:underline ml-1">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>
    @endif

    <!-- Desa Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Kode Desa</th>
                        <th class="py-3 px-4">Nama Desa</th>
                        <th class="py-3 px-4">Puskesmas / Instansi</th>
                        <th class="py-3 px-4">Kecamatan</th>
                        <th class="py-3 px-4">Kabupaten</th>
                        <th class="py-3 px-4 text-center">Petugas (Bidan/Kader)</th>
                        <th class="py-3 px-4 text-center">Ibu Hamil Terdaftar</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($desas as $desa)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-mono text-slate-500">
                                {{ $desa->kode_desa ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <a href="{{ route('desa.show', $desa) }}" class="hover:text-teal-700">
                                    {{ $desa->nama }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($desa->puskesmas)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        {{ $desa->puskesmas }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum Ditentukan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $desa->kecamatan }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $desa->kabupaten }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-700">
                                    {{ $desa->users_count }} Orang
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ $desa->ibu_hamils_count }} Bumil
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('desa.show', $desa) }}" class="text-teal-700 hover:text-teal-800 font-semibold">
                                        Rincian
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('desa.edit', $desa) }}" class="text-blue-600 hover:text-blue-700">
                                            Edit
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <form action="{{ route('desa.destroy', $desa) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus desa ini? Semua data terkait akan terhapus.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-700">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data desa terdaftar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

