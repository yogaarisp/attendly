@extends('layouts.admin', ['title' => 'Laporan Rekapitulasi Presensi'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Laporan Rekapitulasi Presensi</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Filter data kehadiran dan unduh laporan resmi dalam format Excel atau PDF</p>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Export Excel -->
            <a 
                href="{{ route('admin.reports.export.excel', request()->query()) }}" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/20 flex items-center gap-2 transition-all cursor-pointer"
            >
                <i data-lucide="sheet" class="w-4 h-4"></i>
                <span>Export Excel (.xlsx)</span>
            </a>

            <!-- Export PDF -->
            <a 
                href="{{ route('admin.reports.export.pdf', request()->query()) }}" 
                target="_blank"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/20 flex items-center gap-2 transition-all cursor-pointer"
            >
                <i data-lucide="file-text" class="w-4 h-4"></i>
                <span>Export PDF</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm dark:shadow-xl">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            
            <!-- Date From -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
            </div>

            <!-- Branch -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cabang</label>
                <select name="branch_id" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $br)
                        <option value="{{ $br->id }}" {{ request('branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Departemen</label>
                <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Status Kehadiran</label>
                <select name="status" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Status</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Hadir Tepat Waktu</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                    <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Belum Checkout</option>
                </select>
            </div>

            <!-- Filter CTA -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
            </div>

        </form>
    </div>

    <!-- Report Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden">
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
            <span>Ditemukan <strong class="text-slate-900 dark:text-white">{{ $attendances->total() }}</strong> baris catatan presensi</span>
            <span class="font-mono">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-850 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">NIK</th>
                        <th class="px-4 py-3.5">Nama Karyawan</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5">Cabang</th>
                        <th class="px-4 py-3.5">Jam Masuk</th>
                        <th class="px-4 py-3.5">Jam Pulang</th>
                        <th class="px-4 py-3.5">Jarak</th>
                        <th class="px-4 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($attendances as $att)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-850/50 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-slate-900 dark:text-white">{{ $att->attendance_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $att->employee->employee_code }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">{{ $att->employee->full_name }}</td>
                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $att->employee->department->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">{{ $att->branch->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-emerald-600 dark:text-emerald-400">
                                {{ $att->check_in_at ? $att->check_in_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-indigo-600 dark:text-indigo-400">
                                {{ $att->check_out_at ? $att->check_out_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                                {{ $att->check_in_distance ? $att->check_in_distance . 'm' : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                                    {{ $att->check_in_status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">
                                Tidak ada data presensi yang ditemukan untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $attendances->links() }}
        </div>
    </div>

</div>
@endsection
