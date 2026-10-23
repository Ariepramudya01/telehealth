@extends('layouts.app', ['title' => 'Manajemen Pengguna', 'header' => auth()->user()->isAdmin() ? 'Manajemen Pengguna' : 'Kelola Kader Posyandu'])

@section('content')
    <div class="space-y-6">

        <!-- Header Actions -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
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

        <!-- WhatsApp Activation Prompt for Newly Created / Resent User -->
        @if (session('new_user_wa_url') || session('new_user_activation_url'))
            <div x-data="{ copied: false }"
                class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-2xl p-5 shadow-lg shadow-teal-600/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase tracking-wider text-emerald-100">
                            Aktivasi Akun WhatsApp
                        </span>
                        <span class="text-xs text-emerald-100 font-medium">Siap Dikirim ke Pengguna</span>
                    </div>
                    <h3 class="text-base font-bold text-white">
                        Tautan Aktivasi: {{ session('new_user_name', 'Pengguna') }}
                    </h3>
                    <p class="text-xs text-emerald-100 max-w-xl leading-relaxed">
                        Pengguna telah terdaftar di sistem. Kirimkan tautan ini ke nomor WhatsApp (<span
                            class="font-mono font-semibold">{{ session('new_user_phone', '-') }}</span>) agar pengguna dapat
                        langsung mengaktifkan akun dan membuat kata sandi.
                    </p>
                    <div
                        class="pt-1 font-mono text-[11px] text-emerald-200 select-all break-all bg-emerald-800/40 px-3 py-1.5 rounded-lg border border-emerald-400/30">
                        {{ session('new_user_activation_url') }}
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    @if (session('new_user_wa_url'))
                        <a href="{{ session('new_user_wa_url') }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-emerald-800 text-xs font-bold hover:bg-emerald-50 shadow-md transition-all transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 fill-emerald-600" viewBox="0 0 24 24">
                                <path
                                    d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                            </svg>
                            <span>Kirim via WhatsApp</span>
                        </a>
                    @endif
                    <button type="button"
                        @click="navigator.clipboard.writeText('{{ session('new_user_activation_url') }}'); copied = true; setTimeout(() => copied = false, 2500);"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-900/60 hover:bg-emerald-900 text-white text-xs font-semibold border border-white/20 transition-colors">
                        <span x-text="copied ? '✓ Tautan Tersalin!' : '📋 Salin Tautan'"></span>
                    </button>
                </div>
            </div>
        @endif

        <!-- Pending Verification Notification Banner -->
        @if (isset($pendingCount) && $pendingCount > 0)
            <div
                class="bg-amber-50 border border-amber-200/90 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-amber-900">
                            Ada {{ $pendingCount }} Akun Menunggu Verifikasi Admin
                        </h3>
                        <p class="text-[11px] text-amber-700 mt-0.5">
                            Pendaftar baru belum dapat masuk ke sistem sampai akun diverifikasi dan diaktifkan. Klik tombol
                            <strong>Verifikasi Akun</strong> pada tabel di bawah.
                        </p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-200/70 text-amber-800 shrink-0 self-start sm:self-auto">
                    ⏳ {{ $pendingCount }} Menunggu
                </span>
            </div>
        @endif

        <!-- Filter (Admin only) -->
        @if (auth()->user()->isAdmin())
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

                    @if (isset($kecamatanList) && $kecamatanList->isNotEmpty())
                        <div class="flex-1 min-w-[180px]">
                            <select name="kecamatan" onchange="this.form.submit()"
                                class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                                <option value="">-- Semua Kecamatan --</option>
                                @foreach ($kecamatanList as $kec)
                                    <option value="{{ $kec }}"
                                        {{ request('kecamatan') === $kec ? 'selected' : '' }}>
                                        Kec. {{ ucwords(strtolower($kec)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex-1 min-w-[200px]">
                        <select name="desa_id" onchange="this.form.submit()"
                            class="w-full text-xs font-medium rounded-xl border-slate-300 bg-slate-50 text-slate-700 py-2 pl-3 pr-8 focus:ring-teal-500 focus:border-teal-500 shadow-xs">
                            <option value="">-- Semua Wilayah Desa --</option>
                            @foreach ($desas as $d)
                                <option value="{{ $d->id }}" {{ request('desa_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if (request()->hasAny(['role', 'kecamatan', 'desa_id']))
                        <a href="{{ route('users.index') }}"
                            class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200">
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
                        <tr
                            class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
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
                            @if ($u->id === auth()->id())
                                @continue
                            @endif
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <div>{{ $u->name }}</div>
                                    @if ($u->jabatan)
                                        <div class="mt-0.5">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                                {{ $u->jabatan }}
                                            </span>
                                        </div>
                                    @endif
                                    @if ($u->instansi)
                                        <div class="mt-0.5">
                                            <span
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">
                                                🏛️ {{ $u->instansi }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-slate-500 font-normal mt-0.5 space-y-0.5">
                                        @if ($u->nip)
                                            <div class="font-mono text-slate-600">NIP: {{ $u->nip }}</div>
                                        @endif
                                        @if ($u->str)
                                            <div class="font-mono text-slate-500">STR: {{ $u->str }}</div>
                                        @endif
                                        @if ($u->nik)
                                            <div class="font-mono text-slate-400">NIK: {{ $u->nik }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <div>{{ $u->email }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->phone ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($u->isDinkes())
                                        <div class="inline-flex flex-col">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-900 border border-indigo-200">
                                                <span>🏛️</span> Dinkes Kabupaten
                                            </span>
                                            <span class="text-[9px] font-semibold text-indigo-700 mt-0.5">Dinas Kesehatan</span>
                                        </div>
                                    @elseif($u->isAdminPuskesmas())
                                        <div class="inline-flex flex-col">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                                                <span>🏥</span> Admin PKM
                                            </span>
                                            <span class="text-[9px] font-medium text-sky-600 mt-0.5">UPT Puskesmas</span>
                                        </div>
                                    @elseif($u->isBidan())
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-100 text-teal-800">
                                            <span>👩‍⚕️</span> Bidan Desa
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800">
                                            <span>🤝</span> Kader Posyandu
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    @if ($u->desa)
                                        <div class="font-medium">{{ $u->desa->nama }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $u->desa->kecamatan }}</div>
                                    @elseif($u->kecamatan)
                                        <div class="font-medium text-indigo-700">{{ $u->kecamatan }}</div>
                                        <div class="text-[10px] text-slate-400">Admin Puskesmas</div>
                                    @else
                                        <span class="text-slate-500 font-medium">Wilayah Pusat / Seluruh Daerah</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($u->is_active)
                                        <div class="flex flex-col items-center gap-1">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Aktif
                                            </span>
                                            @if (auth()->user()->canToggleStatus($u))
                                                <form action="{{ route('users.toggle-status', $u) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun {{ $u->name }}?');">
                                                    @csrf
                                                    <button type="submit"
                                                        class="text-[10px] text-slate-400 hover:text-rose-600 transition-colors underline decoration-dotted">
                                                        Nonaktifkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center gap-1.5">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Menunggu Aktivasi WA
                                            </span>

                                            @if ($u->phone)
                                                <a href="{{ $u->whatsapp_activation_url }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs transition-colors"
                                                    title="Kirimkan pesan dan tautan aktivasi akun ke WhatsApp pengguna">
                                                    <span>💬 Kirim Link WA</span>
                                                </a>
                                            @endif

                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                @if (auth()->user()->canEditUser($u))
                                                    <form action="{{ route('users.resend-activation', $u) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-[9px] text-teal-700 hover:text-teal-900 font-semibold underline decoration-dotted"
                                                            title="Perbarui token aktivasi">
                                                            Perbarui Link
                                                        </button>
                                                    </form>
                                                    <span class="text-slate-300 text-[10px]">|</span>
                                                @endif
                                                @if (auth()->user()->canToggleStatus($u))
                                                    <form action="{{ route('users.toggle-status', $u) }}" method="POST"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-[9px] text-slate-400 hover:text-slate-700 font-medium underline decoration-dotted"
                                                            title="Aktifkan langsung tanpa verifikasi WA">
                                                            Paksa Aktif
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($u->phone && auth()->user()->canEditUser($u))
                                            <a href="{{ $u->whatsapp_activation_url }}" target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-emerald-600 hover:text-emerald-700 font-semibold text-xs inline-flex items-center gap-0.5"
                                                title="Kirim tautan aktivasi / info via WhatsApp">
                                                💬 WA
                                            </a>
                                            <span class="text-slate-300">|</span>
                                        @endif

                                        @if (auth()->user()->canEditUser($u))
                                            <a href="{{ route('users.edit', $u) }}"
                                                class="text-blue-600 hover:text-blue-700 font-semibold">
                                                Edit
                                            </a>
                                        @endif

                                        @if (auth()->user()->canDeleteUser($u))
                                            @if (auth()->user()->canEditUser($u))
                                                <span class="text-slate-300">|</span>
                                            @endif
                                            <form action="{{ route('users.destroy', $u) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $u->name }} ({{ $u->hierarchy_title }})?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-rose-600 hover:text-rose-700 font-semibold">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif

                                        @if (!auth()->user()->canEditUser($u) && !auth()->user()->canDeleteUser($u))
                                            <span
                                                class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-50 px-2 py-0.5 rounded border border-slate-200">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                Terkunci
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada pengguna ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
