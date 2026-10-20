@extends('layouts.app', ['title' => 'Rincian Desa: ' . $desa->nama, 'header' => 'Rincian Wilayah: ' . $desa->nama])

@section('content')
<div class="space-y-6">

    <!-- Header & Info -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('desa.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800 flex items-center gap-1 mb-2">
                ← Kembali ke Daftar Desa
            </a>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-bold text-slate-800">{{ $desa->nama }}</h2>
                @if($desa->kode_desa)
                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $desa->kode_desa }}</span>
                @endif
                @if($desa->puskesmas)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        {{ $desa->puskesmas }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ $desa->kecamatan }} • {{ $desa->kabupaten }}{{ $desa->provinsi ? ' • ' . $desa->provinsi : '' }}</p>
        </div>

        @if(auth()->user()->isAdmin())
            <div class="flex items-center gap-2">
                <a href="{{ route('desa.edit', $desa) }}" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                    Edit Desa
                </a>
            </div>
        @endif
    </div>

    <!-- Petugas Bidan & Kader Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4">Petugas Pembina di Wilayah Ini</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($desa->users as $u)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full {{ $u->isBidan() ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr($u->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $u->isBidan() ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $u->role->label() }}
                        </span>
                        <h4 class="font-bold text-slate-800 text-sm mt-1 truncate">{{ $u->name }}</h4>
                        <p class="text-xs text-slate-500 truncate">{{ $u->phone ?? 'Tanpa No HP' }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ $u->email }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-6 text-center text-xs text-slate-400">
                    Belum ada petugas (Bidan/Kader) yang ditugaskan di desa ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Ibu Hamil in this Village Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Ibu Hamil di {{ $desa->nama }}</h3>
                <p class="text-xs text-slate-500">Total {{ $desa->ibuHamils->count() }} ibu hamil terdata</p>
            </div>
            <a href="{{ route('ibu-hamil.create') }}?desa_id={{ $desa->id }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                + Daftarkan Ibu Hamil
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Ibu Hamil</th>
                        <th class="py-3 px-4">NIK</th>
                        <th class="py-3 px-4">Usia</th>
                        <th class="py-3 px-4">Taksiran Lahir</th>
                        <th class="py-3 px-4 text-center">Status Skrining SPR</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($desa->ibuHamils as $bumil)
                        @php $latest = $bumil->latestSkrining(); @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $bumil->nama }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $bumil->nik ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $bumil->usia ? $bumil->usia . ' Tahun' : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $bumil->taksiran_persalinan ? $bumil->taksiran_persalinan->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($latest)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $latest->kategori_risiko->badgeClasses() }}">
                                        Skor {{ $latest->total_skor }} • {{ $latest->kategori_risiko->value }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Belum diskrining</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('ibu-hamil.show', $bumil) }}" class="font-semibold text-teal-700 hover:text-teal-800">
                                    Lihat Profil →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada ibu hamil terdaftar di desa ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

