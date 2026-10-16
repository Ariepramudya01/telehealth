@extends('layouts.app', ['title' => 'Manajemen Pengguna', 'header' => auth()->user()->isAdmin() ? 'Manajemen Pengguna' : 'Kelola Kader Posyandu'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                {{ auth()->user()->isAdmin() ? 'Daftar Pengguna Sistem E-Detimil' : 'Daftar Kader Posyandu Desa ' . auth()->user()->desa?->nama }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ auth()->user()->isAdmin() ? 'Kelola akun Admin, Bidan Desa, dan Kader Posyandu' : 'Kelola kader pembina skrining di wilayah desa Anda' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('users.create') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ auth()->user()->isAdmin() ? 'Tambah Pengguna Baru' : 'Tambah Kader Baru' }}</span>
            </a>
        </div>
    </div>

    <!-- Filter (Admin only) -->
    @if(auth()->user()->isAdmin())
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[180px]">
                    <select name="role" onchange="this.form.submit()" 
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Peran Pengguna --</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="bidan" {{ request('role') === 'bidan' ? 'selected' : '' }}>Bidan Desa</option>
                        <option value="kader" {{ request('role') === 'kader' ? 'selected' : '' }}>Kader</option>
                    </select>
                </div>

                <div class="flex-1 min-w-[200px]">
                    <select name="desa_id" onchange="this.form.submit()" 
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                        <option value="">-- Semua Wilayah Desa --</option>
                        @foreach($desas as $d)
                            <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>{{ $d->nama }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request()->hasAny(['role', 'desa_id']))
                    <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>
    @endif

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email & No HP</th>
                        <th class="py-3 px-4">Peran (Role)</th>
                        <th class="py-3 px-4">Wilayah Desa</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $u->name }}
                                @if($u->nik)
                                    <span class="block text-[11px] font-mono text-slate-400 font-normal">NIK: {{ $u->nik }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ $u->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $u->phone ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold
                                    {{ $u->isAdmin() ? 'bg-purple-100 text-purple-800' : ($u->isBidan() ? 'bg-teal-100 text-teal-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $u->role->label() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                {{ $u->desa?->nama ?? 'Wilayah Pusat (Admin)' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($u->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.edit', $u) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                        Edit
                                    </a>

                                    @if(auth()->id() !== $u->id)
                                        <span class="text-slate-300">|</span>
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');" class="inline">
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
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada pengguna ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

