@extends('layouts.app', ['title' => 'Dashboard Bidan Desa', 'header' => 'Dashboard Bidan: ' . $desa->nama])

@section('content')
<div class="space-y-6">

    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-800">
                    Bidan Desa Terdaftar
                </span>
                <span class="text-xs text-slate-500">{{ $desa->kecamatan }}, {{ $desa->kabupaten }}</span>
            </div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight mt-1">{{ $desa->nama }}</h2>
            <p class="text-xs text-slate-500">Pantau deteksi risiko dini ibu hamil dan pembinaan kader posyandu di wilayah kerja Anda</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('laporan.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-700 text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Unduh Laporan Desa</span>
            </a>

            <a href="{{ route('users.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>Tambah Kader Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ibu Hamil Desa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ibu Hamil Terdaftar</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $bumilCount }}</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ count($kaders) }} Kader Pembina</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                Input Mandiri Bidan: <strong>{{ $myBumilCount }}</strong> bumil
            </div>
        </div>

        <!-- Capaian Hadir -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Hadir</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenHadir }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalHadir }} dari {{ $totalKunjungan }} kunjungan</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $persenHadir) }}%"></div>
            </div>
        </div>

        <!-- Capaian Dilayani -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Capaian Dilayani</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenDilayani }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalDilayani }} terlayani lengkap</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $persenDilayani) }}%"></div>
            </div>
        </div>

        <!-- Cakupan Skrining SPR -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Cakupan Skrining SPR</span>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1">{{ $persenSkrining }}%</h3>
                    <span class="text-xs text-slate-500 mt-1 block">{{ $totalSkrining }} skrining selesai</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-emerald-700">KRR: {{ $skriningKRR }}</span>
                <span class="text-amber-700">KRT: {{ $skriningKRT }}</span>
                <span class="text-rose-700 font-bold">KRST: {{ $skriningKRST }}</span>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Diagram Kunjungan Desa -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-1">Status Kunjungan Ibu Hamil (Desa {{ $desa->nama }})</h3>
            <p class="text-xs text-slate-500 mb-4">Perbandingan kehadiran dan pelayanan ANC</p>
            <div class="relative h-64">
                <canvas id="bidanKunjunganChart"></canvas>
            </div>
        </div>

        <!-- Diagram Risiko Desa -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <h3 class="text-base font-bold text-slate-800 mb-1">Sebaran Tingkat Risiko (Skor Poedji Rochjati)</h3>
            <p class="text-xs text-slate-500 mb-4">Proporsi kehamilan risiko rendah, tinggi, dan sangat tinggi</p>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="bidanRisikoChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Kinerja Kader di Desa Ini -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Capaian Kinerja Kader Posyandu (Desa {{ $desa->nama }})</h3>
                <p class="text-xs text-slate-500">Monitoring produktivitas pendataan dan skrining kader binaan</p>
            </div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">
                Kelola Seluruh Kader →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Kader</th>
                        <th class="py-3 px-4">No Handphone</th>
                        <th class="py-3 px-4 text-center">Bumil Diinput</th>
                        <th class="py-3 px-4 text-center">Skrining Dijalankan</th>
                        <th class="py-3 px-4 text-center">Temuan KRST</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kaders as $k)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $k['name'] }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $k['phone'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-800">
                                {{ $k['bumil_count'] }} Orang
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-teal-700">
                                {{ $k['skrining_count'] }} Kali
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($k['krst_found'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                        {{ $k['krst_found'] }} Kasus
                                    </span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada kader terdaftar di desa ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert KRST di desa -->
    @if($urgentKRST->count() > 0)
        <div class="bg-rose-50/80 rounded-2xl border border-rose-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                    <h3 class="text-sm font-bold text-rose-900 uppercase tracking-wider">Pasien Risiko Sangat Tinggi (KRST) di Desa {{ $desa->nama }}</h3>
                </div>
                <span class="text-xs text-rose-700 font-semibold">Segera Lakukan Rujukan</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($urgentKRST as $u)
                    <div class="bg-white p-4 rounded-xl border border-rose-200 shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-full">
                                Skor {{ $u->total_skor }} (KRST)
                            </span>
                            <span class="text-[11px] text-slate-400">{{ $u->tanggal_skrining->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="font-bold text-slate-800 mt-2">{{ $u->ibuHamil->nama }}</h4>
                        <p class="text-xs text-slate-500">Usia {{ $u->ibuHamil->usia ?? '-' }} Th • Diskrining oleh {{ $u->user->name }}</p>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex justify-end">
                            <a href="{{ route('skrining.show', $u) }}" class="text-xs font-semibold text-rose-700 hover:text-rose-800">
                                Rincian Jawaban SPR →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Kunjungan Chart
        const ctxKunjungan = document.getElementById('bidanKunjunganChart').getContext('2d');
        new Chart(ctxKunjungan, {
            type: 'bar',
            data: {
                labels: ['Total Jadwal', 'Hadir', 'Dilayani'],
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: [{{ $totalKunjungan }}, {{ $totalHadir }}, {{ $totalDilayani }}],
                    backgroundColor: ['#94a3b8', '#3b82f6', '#10b981'],
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Risiko Chart
        const ctxRisiko = document.getElementById('bidanRisikoChart').getContext('2d');
        new Chart(ctxRisiko, {
            type: 'doughnut',
            data: {
                labels: ['KRR (Risiko Rendah)', 'KRT (Risiko Tinggi)', 'KRST (Sangat Tinggi)'],
                datasets: [{
                    data: [{{ $skriningKRR }}, {{ $skriningKRT }}, {{ $skriningKRST }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%'
            }
        });
    });
</script>
@endpush

