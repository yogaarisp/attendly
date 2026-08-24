@extends('layouts.admin', ['title' => 'Monitoring Presensi'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Monitoring Absensi Karyawan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pantau kehadiran harian, jam masuk, jam pulang, dan status keterlambatan</p>
        </div>
        <div class="text-xs font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-3 py-1.5 rounded-xl text-slate-700 dark:text-slate-300 shadow-sm">
            Tanggal: <strong class="text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</strong>
        </div>
    </div>

    <!-- Suspicious Alert Banner — tampil jika ada absensi mencurigakan hari ini -->
    @php
        $suspiciousCount = $attendances->where('is_suspicious', true)->count()
            + \App\Models\Attendance::where('attendance_date', $date)->where('is_suspicious', true)->count();
        $suspiciousCount = \App\Models\Attendance::where('attendance_date', $date)->where('is_suspicious', true)->count();
    @endphp
    @if ($suspiciousCount > 0)
        <div class="flex items-start gap-3 p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-2xl text-xs text-rose-700 dark:text-rose-300">
            <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 mt-0.5 text-rose-500"></i>
            <div>
                <p class="font-bold">Terdeteksi {{ $suspiciousCount }} absensi mencurigakan pada tanggal ini.</p>
                <p class="text-rose-600 dark:text-rose-400 mt-0.5">Baris yang ditandai <span class="font-bold">⚠ Fake GPS</span> perlu diperiksa lebih lanjut oleh admin. Klik "Detail Presensi" untuk melihat alasan kecurigaan.</p>
            </div>
        </div>
    @endif

    <!-- Quick Date Metric Pills -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-sm dark:shadow-md transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Hadir</span>
            <span class="text-2xl font-black text-brand-600 dark:text-brand-400 mt-1 block">{{ $stats['total_present'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-sm dark:shadow-md transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Tepat Waktu</span>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['on_time'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-sm dark:shadow-md transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Terlambat</span>
            <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['late'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-sm dark:shadow-md transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Checkout</span>
            <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block">{{ $stats['pending_checkout'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shadow-sm dark:shadow-md transition-colors">
            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Belum Hadir</span>
            <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block">{{ $stats['absent'] }}</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg transition-colors">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Kantor Cabang</label>
                <select name="branch_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $br)
                        <option value="{{ $br->id }}" {{ $branchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Departemen</label>
                <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Pencarian</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIK..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-brand-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Departemen</th>
                        <th class="px-4 py-3.5">Cabang</th>
                        <th class="px-4 py-3.5">Jam Masuk</th>
                        <th class="px-4 py-3.5">Jam Pulang</th>
                        <th class="px-4 py-3.5">Jarak (m)</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($attendances as $att)
                        {{-- Baris suspicious mendapat highlight latar merah tipis --}}
                        <tr class="{{ $att->is_suspicious ? 'bg-rose-50 dark:bg-rose-500/5 hover:bg-rose-100 dark:hover:bg-rose-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-850/50' }} transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-600 dark:text-brand-300 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($att->employee->full_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block">{{ $att->employee->full_name }}</span>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ $att->employee->employee_code }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">{{ $att->employee->department->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">{{ $att->branch->name }}</td>
                            <td class="px-4 py-3.5 font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                {{ $att->check_in_at ? $att->check_in_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                {{ $att->check_out_at ? $att->check_out_at->format('H:i:s') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600 dark:text-slate-300">
                                {{ $att->check_in_distance ? $att->check_in_distance . 'm' : '-' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1">
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-700 dark:text-amber-300' }}">
                                        {{ $att->check_in_status_label }}
                                    </span>
                                    @if ($att->is_suspicious)
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center gap-1 w-fit">
                                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                            Fake GPS?
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a href="{{ route('admin.attendance.show', $att->id) }}" class="{{ $att->is_suspicious ? 'bg-rose-100 dark:bg-rose-500/20 hover:bg-rose-200 dark:hover:bg-rose-500/30 text-rose-700 dark:text-rose-300' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200' }} px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="{{ $att->is_suspicious ? 'shield-alert' : 'eye' }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $att->is_suspicious ? 'Periksa' : 'Detail' }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data presensi yang sesuai pada tanggal {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
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
