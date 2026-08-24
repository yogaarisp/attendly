@extends('layouts.admin', ['title' => 'Dashboard Overview'])

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards Grid (PRD section 33) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Total Employees -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg flex items-center justify-between transition-colors">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Karyawan</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block">{{ $totalEmployees }}</span>
                <span class="text-[10px] text-slate-500">{{ $activeEmployees }} Karyawan Aktif</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg flex items-center justify-between transition-colors">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Hadir Hari Ini</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $totalPresentToday }}</span>
                <span class="text-[10px] text-slate-500">Tepat Waktu: {{ $onTimeCount }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Terlambat -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg flex items-center justify-between transition-colors">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Terlambat</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $lateCount }}</span>
                <span class="text-[10px] text-slate-500">Lewat batas toleransi</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="clock-alert" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Belum Checkout -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg flex items-center justify-between transition-colors">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Pulang</span>
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ $pendingCheckoutCount }}</span>
                <span class="text-[10px] text-slate-500">Masih di kantor</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                <i data-lucide="building-2" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Tidak Hadir / Alpha -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg flex items-center justify-between transition-colors">
            <div>
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Hadir</span>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ $absentCount }}</span>
                <span class="text-[10px] text-slate-500">Belum check-in</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <i data-lucide="user-x" class="w-5 h-5"></i>
            </div>
        </div>

    </div>

    <!-- Charts & Branch Overview Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 7-Day Trend Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm dark:shadow-lg transition-colors">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Tren Kehadiran (7 Hari Terakhir)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Statistik perbandingan hadir tepat waktu vs terlambat</p>
                </div>
            </div>
            <div class="h-64 w-full">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>

        <!-- Branch Distribution -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm dark:shadow-lg flex flex-col justify-between transition-colors">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight mb-3">Distribusi Kantor Cabang</h3>
                <div class="space-y-3">
                    @foreach ($branches as $branch)
                        <div class="p-3 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $branch->name }}</span>
                                <span class="text-brand-600 dark:text-brand-400 font-mono font-bold">{{ $branch->employees_count }} Karyawan</span>
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-2">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="crosshair" class="w-3 h-3 text-slate-400 dark:text-slate-500"></i>
                                    Radius {{ $branch->radius_meter }}m
                                </span>
                                <span>•</span>
                                <span>{{ $branch->timezone }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('admin.branches.index') }}" class="mt-4 w-full py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-xl text-xs font-semibold text-center transition-colors block">
                Kelola Cabang & Lokasi
            </a>
        </div>

    </div>

    <!-- Today's Recent Live Attendance Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden transition-colors">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Presensi Hari Ini (Live Monitoring)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar kehadiran real-time karyawan hari ini</p>
            </div>
            <a href="{{ route('admin.attendance.index') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 flex items-center gap-1">
                <span>Lihat Selengkapnya</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-850 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-4 py-3">Departemen</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Jam Masuk</th>
                        <th class="px-4 py-3">Jam Pulang</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($recentToday as $att)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-850/50 transition-colors">
                            <td class="px-5 py-3.5 flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-600 dark:text-brand-300 font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($att->employee->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $att->employee->full_name }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $att->employee->employee_code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">{{ $att->employee->department->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">{{ $att->branch->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $att->check_in_at ? $att->check_in_at->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-3.5 font-mono text-indigo-600 dark:text-indigo-400 font-semibold">{{ $att->check_out_at ? $att->check_out_at->format('H:i:s') : '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-700 dark:text-amber-300' }}">
                                    {{ $att->check_in_status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.attendance.show', $att->id) }}" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-[11px] font-medium transition-colors inline-flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3 h-3"></i>
                                    <span>Detail</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada presensi yang tercatat hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
    const isDarkMode = document.documentElement.classList.contains('dark');
    const gridColor = isDarkMode ? '#1e293b' : '#f1f5f9';
    const textColor = isDarkMode ? '#94a3b8' : '#64748b';

    new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: @json($trendDates),
            datasets: [
                {
                    label: 'Tepat Waktu',
                    data: @json($trendPresent),
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                },
                {
                    label: 'Terlambat',
                    data: @json($trendLate),
                    backgroundColor: '#f59e0b',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 11 } }
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 } }
                },
                y: {
                    grid: { color: gridColor },
                    ticks: { color: textColor, font: { family: 'Plus Jakarta Sans', size: 10 }, stepSize: 1 },
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush
