@extends('layouts.employee', ['title' => 'Dashboard Karyawan'])

@section('content')
<div class="space-y-4">
    
    <!-- Greeting Card & Realtime Clock -->
    <div class="bg-gradient-to-br from-brand-50 to-indigo-100 dark:from-slate-800 dark:to-slate-850 border border-brand-100 dark:border-slate-700/60 rounded-3xl p-5 shadow-sm dark:shadow-xl relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-brand-600/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center justify-between">
            <div>
                <p id="greeting-text" class="text-xs font-semibold text-brand-600 dark:text-brand-400">Selamat Beraktivitas,</p>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight mt-0.5">{{ $employee->full_name }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $employee->position->name ?? 'Staff' }} • {{ $employee->department->name ?? 'General' }}</p>
            </div>
            
            <!-- Realtime Clock Widget -->
            <div class="text-right bg-white/80 dark:bg-slate-900/80 border border-brand-100 dark:border-slate-700/60 rounded-2xl px-3.5 py-2 shadow-inner">
                <div id="live-clock" class="text-base font-extrabold text-slate-900 dark:text-white font-mono tracking-wider">--:--:--</div>
                <div class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ date('d M Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Main Attendance Action Card -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-5 shadow-sm dark:shadow-xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4 text-brand-500 dark:text-brand-400"></i>
                <span>Absensi Hari Ini</span>
            </h3>
            <span class="text-[11px] font-medium text-slate-400">{{ $branch->name }}</span>
        </div>

        @if (!$isWorkingDay)
            <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-amber-600 dark:text-amber-400 text-center">
                <i data-lucide="calendar-off" class="w-8 h-8 mx-auto mb-2"></i>
                <p class="text-sm font-semibold">Hari ini bukan hari kerja</p>
                <p class="text-xs text-amber-500 dark:text-amber-300/80 mt-1">Sistem absensi non-aktif di luar jadwal hari kerja normal.</p>
            </div>
        @else
            <!-- Attendance States -->
            @if (!$todayAttendance || !$todayAttendance->hasCheckedIn())
                <!-- State 1: Belum Check-in -->
                <div class="text-center py-4">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-700/80 flex items-center justify-center text-slate-400 mx-auto mb-3 shadow-inner">
                        <i data-lucide="scan-face" class="w-8 h-8 text-brand-500 dark:text-brand-400"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Belum Melakukan Absen Masuk</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jam masuk standar: {{ substr($setting->work_start_time ?? '08:00', 0, 5) }} (Toleransi {{ $setting->late_tolerance_minutes ?? 15 }} mnt)</p>

                    <a href="{{ route('employee.attendance.checkin') }}" class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-brand-500/30 active:scale-95 transition-all">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                        <span>ABSEN MASUK SEKARANG</span>
                    </a>
                </div>
            @elseif ($todayAttendance->hasCheckedIn() && !$todayAttendance->hasCheckedOut())
                <!-- State 2: Sudah Check-in, Belum Check-out -->
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Masuk</span>
                            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block">{{ $todayAttendance->check_in_at->format('H:i:s') }}</span>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $todayAttendance->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                                {{ $todayAttendance->check_in_status_label }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pulang</span>
                            <span class="text-base font-extrabold text-slate-400 font-mono mt-0.5 block">--:--:--</span>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                Menunggu Jam Pulang
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('employee.attendance.checkout') }}" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-amber-500/30 active:scale-95 transition-all">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                        <span>ABSEN PULANG</span>
                    </a>
                </div>
            @else
                <!-- State 3: Selesai Check-in & Check-out -->
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Masuk</span>
                            <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block">{{ $todayAttendance->check_in_at->format('H:i:s') }}</span>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-300">
                                {{ $todayAttendance->check_in_status_label }}
                            </span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pulang</span>
                            <span class="text-base font-extrabold text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 block">{{ $todayAttendance->check_out_at->format('H:i:s') }}</span>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-300">
                                Selesai
                            </span>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Absensi hari ini telah lengkap dan terverifikasi.</span>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Monthly Summary Stats -->
    <div class="grid grid-cols-3 gap-2.5">
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Tepat Waktu</span>
            <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $monthStats['present'] }}</span>
            <span class="text-[9px] text-slate-400 dark:text-slate-500">Bulan Ini</span>
        </div>
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Terlambat</span>
            <span class="text-lg font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">{{ $monthStats['late'] }}</span>
            <span class="text-[9px] text-slate-400 dark:text-slate-500">Bulan Ini</span>
        </div>
        <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-2xl p-3 text-center">
            <span class="text-[10px] font-bold text-slate-400 block">Total Hadir</span>
            <span class="text-lg font-extrabold text-brand-600 dark:text-brand-400 mt-1 block">{{ $monthStats['total'] }}</span>
            <span class="text-[9px] text-slate-400 dark:text-slate-500">Bulan Ini</span>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-5 shadow-sm dark:shadow-xl">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Aktivitas Terakhir</h3>
            <a href="{{ route('employee.history') }}" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-500 dark:hover:text-brand-300">Lihat Semua</a>
        </div>

        @if ($recentAttendances->isEmpty())
            <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">
                <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                Belum ada data kehadiran tercatat.
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach ($recentAttendances as $att)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400' }} flex items-center justify-center text-xs font-bold">
                                <i data-lucide="{{ $att->check_in_status === 'on_time' ? 'check' : 'clock' }}" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ $att->attendance_date->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ $att->check_in_at ? $att->check_in_at->format('H:i') : '--:--' }} - 
                                    {{ $att->check_out_at ? $att->check_out_at->format('H:i') : '--:--' }}
                                </p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $att->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                            {{ $att->check_in_status_label }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Live Realtime Clock
    function updateClock() {
        const now = new Date();
        const hrs = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const secs = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('live-clock');
        if (clockEl) {
            clockEl.textContent = `${hrs}:${mins}:${secs}`;
        }

        // Dynamic greeting
        const greetingEl = document.getElementById('greeting-text');
        if (greetingEl) {
            const h = now.getHours();
            if (h < 11) greetingEl.textContent = 'Selamat Pagi,';
            else if (h < 15) greetingEl.textContent = 'Selamat Siang,';
            else if (h < 18) greetingEl.textContent = 'Selamat Sore,';
            else greetingEl.textContent = 'Selamat Malam,';
        }
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endpush
