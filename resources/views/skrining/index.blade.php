@extends('layouts.app', ['title' => 'Riwayat Skrining SPR', 'header' => 'Skrining Skor Poedji Rochjati'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Riwayat Evaluasi Skrining Poedji Rochjati</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pemeriksaan deteksi dini faktor risiko obstetri pada ibu hamil</p>
        </div>

        <a href="{{ route('skrining.create') }}" 
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Input Skrining Baru</span>
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('skrining.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama ibu hamil atau NIK..."
                       class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
            </div>

            <div class="min-w-[180px]">
                <select name="kategori" onchange="this.form.submit()" 
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                    <option value="">-- Semua Kategori Risiko --</option>
                    <option value="KRR" {{ request('kategori') === 'KRR' ? 'selected' : '' }}>KRR (Risiko Rendah - Hijau)</option>
                    <option value="KRT" {{ request('kategori') === 'KRT' ? 'selected' : '' }}>KRT (Risiko Tinggi - Kuning)</option>
                    <option value="KRST" {{ request('kategori') === 'KRST' ? 'selected' : '' }}>KRST (Risiko Sangat Tinggi - Merah)</option>
                </select>
            </div>

            @if(request()->hasAny(['search', 'kategori']))
                <a href="{{ route('skrining.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                    Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Skrining Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Tanggal Skrining</th>
                        <th class="py-3 px-4">Nama Ibu Hamil</th>
                        <th class="py-3 px-4">Desa</th>
                        <th class="py-3 px-4">Usia Kehamilan</th>
                        <th class="py-3 px-4 text-center">Total Skor</th>
                        <th class="py-3 px-4 text-center">Tingkat Kategori Risiko</th>
                        <th class="py-3 px-4">Pemeriksa (Bidan/Kader)</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($skrinings as $s)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $s->tanggal_skrining->translatedFormat('d F Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('ibu-hamil.show', $s->ibuHamil) }}" class="font-bold text-slate-800 hover:text-teal-700 block">
                                    {{ $s->ibuHamil->nama }}
                                </a>
                                @if($s->ibuHamil->nik)
                                    <span class="text-[10px] font-mono text-slate-400">NIK: {{ $s->ibuHamil->nik }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $s->ibuHamil->desa?->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $s->usia_kehamilan_minggu ? $s->usia_kehamilan_minggu . ' Minggu' : '-' }}
                                @if($s->trimester)
                                    <span class="text-slate-400 font-normal">(Tri. {{ $s->trimester }})</span>
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
                                <span class="font-semibold text-slate-800 block">{{ $s->user?->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $s->user?->role->label() }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('skrining.show', $s) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                        Jawaban
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('skrining.print', $s) }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                        Cetak
                                    </a>
                                    @if(!auth()->user()->isKader())
                                        <span class="text-slate-300">|</span>
                                        <form action="{{ route('skrining.destroy', $s) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data skrining ini?');" class="inline">
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
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data skrining SPR ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($skrinings->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $skrinings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

