@extends('layouts.employee', ['title' => 'Riwayat Absensi'])

@section('content')
<div class="space-y-4">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Riwayat Absensi</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Rekapitulasi kehadiran dan catatan waktu kerja</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-4 shadow-sm dark:shadow-xl">
        <form method="GET" action="{{ route('employee.history') }}" class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Bulan</label>
                <select name="month" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    @foreach(range(1, 12) as $m)
                        @php $mStr = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                        <option value="{{ $mStr }}" {{ $month == $mStr ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Tahun</label>
                <select name="year" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 pt-1">
                <button type="submit" class="w-full py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Monthly Stats Summary -->
    <div class="grid grid-cols-3 gap-2">
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Tepat Waktu</span>
            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5 block">{{ $stats['present'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Terlambat</span>
            <span class="text-base font-extrabold text-amber-600 dark:text-amber-400 mt-0.5 block">{{ $stats['late'] }}</span>
        </div>
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Total Hadir</span>
            <span class="text-base font-extrabold text-brand-600 dark:text-brand-400 mt-0.5 block">{{ $stats['total'] }}</span>
        </div>
    </div>

    <!-- Attendance Cards List -->
    <div class="space-y-2.5">
        @if ($attendances->isEmpty())
            <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-8 text-center text-slate-400 dark:text-slate-400 text-xs">
                <i data-lucide="calendar-x" class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-500"></i>
                <p class="font-semibold text-slate-500 dark:text-slate-300">Tidak ada riwayat absensi</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Tidak ditemukan data pada periode yang dipilih.</p>
            </div>
        @else
            @foreach ($attendances as $att)
                <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3.5 shadow-sm dark:shadow-md flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }} flex items-center justify-center font-bold text-sm">
                            {{ $att->attendance_date->format('d') }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $att->attendance_date->translatedFormat('l, d M Y') }}</h4>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Masuk: <strong class="text-slate-700 dark:text-slate-200">{{ $att->check_in_at ? $att->check_in_at->format('H:i') : '--:--' }}</strong> • 
                                Pulang: <strong class="text-slate-700 dark:text-slate-200">{{ $att->check_out_at ? $att->check_out_at->format('H:i') : '--:--' }}</strong>
                            </p>
                            @if ($att->check_in_distance)
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Jarak: {{ $att->check_in_distance }}m dari kantor</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-right flex flex-col items-end gap-1">
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                            {{ $att->check_in_status_label }}
                        </span>
                        @if ($att->check_in_photo)
                            <a href="{{ route('attendance.photo', ['attendance' => $att->id, 'type' => 'check_in']) }}" target="_blank" class="text-[10px] font-medium text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300 flex items-center gap-1">
                                <i data-lucide="image" class="w-3 h-3"></i>
                                <span>Foto</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-2">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
