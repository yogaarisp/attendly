@extends('layouts.admin', ['title' => 'Pengaturan Jam & Hari Kerja'])

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Pengaturan Jam & Jadwal Kerja</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Atur jam masuk, jam pulang, toleransi, batas akurasi GPS, dan hari kerja aktif</p>
        </div>
    </div>

    <!-- Branch Selector Filter -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg transition-colors">
        <form method="GET" action="{{ route('admin.settings.attendance') }}" class="flex items-center gap-3">
            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 whitespace-nowrap">Pilih Kantor Cabang:</label>
            <div class="flex-1 sm:max-w-xs">
                <select name="branch_id" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    @foreach ($branches as $br)
                        <option value="{{ $br->id }}" {{ $selectedBranch && $selectedBranch->id == $br->id ? 'selected' : '' }}>
                            {{ $br->name }} ({{ $br->timezone }})
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if ($selectedBranch && $setting)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-xl transition-colors">
            <form method="POST" action="{{ route('admin.settings.attendance.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">

                <!-- Work Hours Section -->
                <div>
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="clock" class="w-4 h-4 text-brand-600 dark:text-brand-400"></i>
                        <span>Jam Kerja Standar (Cabang: {{ $selectedBranch->name }})</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Masuk Kerja <span class="text-rose-500">*</span></label>
                            <input type="time" name="work_start_time" value="{{ old('work_start_time', substr($setting->work_start_time, 0, 5)) }}" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Pulang Kerja <span class="text-rose-500">*</span></label>
                            <input type="time" name="work_end_time" value="{{ old('work_end_time', substr($setting->work_end_time, 0, 5)) }}" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span></label>
                            <input type="number" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', $setting->late_tolerance_minutes) }}" min="0" max="180" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Check-in sebelum batas ini dihitung Tepat Waktu (On Time).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Batas Minimal Akurasi GPS (Meter) <span class="text-rose-500">*</span></label>
                            <input type="number" name="minimum_gps_accuracy" value="{{ old('minimum_gps_accuracy', $setting->minimum_gps_accuracy) }}" min="10" max="500" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Mencegah sinyal GPS lemah / manipulasi lokasi.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Sistem Absensi</label>
                            <select name="attendance_enabled" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                                <option value="1" {{ old('attendance_enabled', $setting->attendance_enabled) ? 'selected' : '' }}>Aktif (Karyawan dapat absen)</option>
                                <option value="0" {{ !old('attendance_enabled', $setting->attendance_enabled) ? 'selected' : '' }}>Non-Aktif (Absensi ditutup)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Working Days Section -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-brand-600 dark:text-brand-400"></i>
                        <span>Konfigurasi Hari Kerja Aktif (Global)</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Pilih hari yang dihitung sebagai hari kerja normal:</p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        @php
                            $days = [
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                0 => 'Minggu',
                            ];
                        @endphp

                        @foreach ($days as $dayIndex => $dayName)
                            @php
                                $isWorking = isset($workingDays[$dayIndex]) ? $workingDays[$dayIndex]->is_working_day : ($dayIndex >= 1 && $dayIndex <= 5);
                            @endphp
                            <label class="p-3 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center justify-between cursor-pointer hover:border-brand-400 dark:hover:border-slate-600 transition-colors">
                                <span class="text-xs font-semibold text-slate-800 dark:text-white">{{ $dayName }}</span>
                                <input type="checkbox" name="working_days[{{ $dayIndex }}]" value="1" {{ $isWorking ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-brand-600 focus:ring-brand-500">
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Save Button -->
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/25 transition-all cursor-pointer">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
