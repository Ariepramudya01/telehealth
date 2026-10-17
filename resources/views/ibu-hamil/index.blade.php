@extends('layouts.app', ['title' => 'Data Ibu Hamil', 'header' => 'Data Ibu Hamil'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Daftar Ibu Hamil Terdata</h2>
            <p class="text-xs text-slate-500 mt-0.5">Basis data ibu hamil untuk monitoring deteksi dini risiko kehamilan</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('ibu-hamil.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Daftarkan Ibu Hamil Baru</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('ibu-hamil.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search Text -->
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama ibu, NIK, atau suami..."
                       class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-800 py-2 px-3 focus:ring-teal-500 focus:border-teal-500 shadow-xs placeholder-slate-400" />
            </div>

            <!-- Desa Filter (Admin only) -->
            @if(auth()->user()->isAdmin())
                <div>
                    <select name="desa_id" onchange="this.form.submit()" 
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Wilayah Desa --</option>
                        @foreach($desas as $d)
                            <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Risk Category Filter -->
            <div>
                <select name="risiko" onchange="this.form.submit()" 
                        class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                    <option value="">-- Semua Kategori Risiko --</option>
                    <option value="KRR" {{ request('risiko') === 'KRR' ? 'selected' : '' }}>KRR (Risiko Rendah - Hijau)</option>
                    <option value="KRT" {{ request('risiko') === 'KRT' ? 'selected' : '' }}>KRT (Risiko Tinggi - Kuning)</option>
                    <option value="KRST" {{ request('risiko') === 'KRST' ? 'selected' : '' }}>KRST (Risiko Sangat Tinggi - Merah)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['search', 'desa_id', 'risiko']))
                    <a href="{{ route('ibu-hamil.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Ibu Hamil Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Ibu Hamil</th>
                        <th class="py-3 px-4">Usia & Paritas (G-P-A)</th>
                        <th class="py-3 px-4">Wilayah Desa</th>
                        <th class="py-3 px-4">Taksiran Persalinan</th>
                        <th class="py-3 px-4 text-center">Status Skor SPR</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ibuHamils as $bumil)
                        @php $latest = $bumil->latestSkrining(); @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('ibu-hamil.show', $bumil) }}" class="font-bold text-slate-800 hover:text-teal-700 block">
                                    {{ $bumil->nama }}
                                </a>
                                <span class="text-[11px] text-slate-400">
                                    Suami: {{ $bumil->nama_suami ?? '-' }} • HP: {{ $bumil->no_hp ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ $bumil->usia ? $bumil->usia . ' Th' : '-' }}</div>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-mono font-medium">
                                    G{{ $bumil->gravida }} P{{ $bumil->para }} A{{ $bumil->abortus }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $bumil->desa?->nama ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ $bumil->taksiran_persalinan ? $bumil->taksiran_persalinan->format('d/m/Y') : '-' }}</div>
                                @if($bumil->usia_kehamilan_minggu)
                                    <span class="text-[11px] text-teal-600 font-medium">± {{ $bumil->usia_kehamilan_minggu }} mg</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($latest)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $latest->kategori_risiko->badgeClasses() }}">
                                        Skor {{ $latest->total_skor }} • {{ $latest->kategori_risiko->value }}
                                    </span>
                                @else
                                    <a href="{{ route('skrining.create') }}?ibu_hamil_id={{ $bumil->id }}" 
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200">
                                        + Skrining Sekarang
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('ibu-hamil.show', $bumil) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                        Rincian
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('ibu-hamil.edit', $bumil) }}" class="text-blue-600 hover:text-blue-700">
                                        Edit
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <form action="{{ route('ibu-hamil.destroy', $bumil) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ibu hamil ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-700">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data ibu hamil terdaftar</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ibuHamils->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $ibuHamils->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

