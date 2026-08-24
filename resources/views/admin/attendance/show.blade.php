@extends('layouts.admin', ['title' => 'Detail Presensi Karyawan'])

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Detail Presensi: {{ $attendance->employee->full_name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Pemeriksaan bukti foto selfie, validasi GPS, dan kepatuhan radius kantor</p>
        </div>
        <a href="{{ route('admin.attendance.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Summary Overview Banner -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-brand-500/20 text-brand-600 dark:text-brand-300 font-black text-xl flex items-center justify-center border border-brand-500/30">
                {{ strtoupper(substr($attendance->employee->full_name, 0, 1)) }}
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $attendance->employee->full_name }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $attendance->employee->employee_code }} • {{ $attendance->employee->department->name ?? '-' }} ({{ $attendance->employee->position->name ?? '-' }})</p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Penempatan: {{ $attendance->branch->name }}</p>
            </div>
        </div>

        <div class="text-right">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Presensi</span>
            <span class="inline-block mt-1 px-3 py-1 text-xs font-bold rounded-full {{ $attendance->check_in_status === 'on_time' ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300' }}">
                {{ $attendance->check_in_status_label }}
            </span>
            @if ($attendance->is_suspicious)
                <span class="inline-flex items-center gap-1 mt-1.5 px-3 py-1 text-xs font-bold rounded-full bg-rose-500/20 text-rose-600 dark:text-rose-300">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    Fake GPS Terdeteksi
                </span>
            @endif
        </div>
    </div>

    {{-- ── Suspicious / Fake GPS Alert Card ────────────────────────────────── --}}
    @if ($attendance->is_suspicious)
        <div class="bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/40 rounded-3xl p-5 shadow-sm dark:shadow-lg space-y-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-500 flex items-center justify-center shrink-0">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-700 dark:text-rose-300">Absensi Ini Terdeteksi Mencurigakan</h3>
                    <p class="text-xs text-rose-600 dark:text-rose-400">Sistem mendeteksi indikasi penggunaan Fake GPS. Harap verifikasi manual dengan karyawan bersangkutan.</p>
                </div>
            </div>

            <div class="space-y-1.5">
                @foreach ($attendance->suspicious_reasons ?? [] as $reason)
                    <div class="flex items-start gap-2 text-xs text-rose-700 dark:text-rose-300">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0 mt-0.5 text-rose-500"></i>
                        <span>{{ $reason }}</span>
                    </div>
                @endforeach
            </div>

            @if ($attendance->gps_samples && count($attendance->gps_samples) > 0)
                <details class="text-xs">
                    <summary class="font-semibold text-rose-600 dark:text-rose-400 cursor-pointer select-none hover:text-rose-700 dark:hover:text-rose-300">
                        Lihat {{ count($attendance->gps_samples) }} raw GPS sample yang dikirim browser
                    </summary>
                    <div class="mt-2 space-y-1.5 font-mono text-[10px]">
                        @foreach ($attendance->gps_samples as $i => $sample)
                            <div class="p-2 bg-rose-100/60 dark:bg-rose-500/10 rounded-xl border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300">
                                <span class="font-bold">#{{ $i + 1 }}</span>
                                Lat: {{ $sample['lat'] }}, Lng: {{ $sample['lng'] }},
                                Akurasi: {{ $sample['accuracy'] }}m
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    @endif

    <!-- Selfie Verification Photos (Check In & Check Out) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Check In Photo Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm dark:shadow-lg space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="camera" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                    <span>Foto Selfie Absen Masuk</span>
                </span>
                <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : '-' }} WIB
                </span>
            </div>

            <div class="w-full aspect-square bg-slate-100 dark:bg-slate-950 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex items-center justify-center relative">
                @if ($attendance->check_in_photo)
                    <img 
                        src="{{ route('attendance.photo', ['attendance' => $attendance->id, 'type' => 'check_in']) }}" 
                        alt="Foto Masuk" 
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="text-center text-slate-400 dark:text-slate-500 text-xs">
                        <i data-lucide="image-off" class="w-8 h-8 mx-auto mb-1 opacity-40"></i>
                        <span>Foto tidak tersedia (Record lama / Manual)</span>
                    </div>
                @endif
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-850 rounded-xl text-[11px] space-y-1 font-mono text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Koordinat:</span>
                    <span>{{ $attendance->check_in_latitude ?? '-' }}, {{ $attendance->check_in_longitude ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Akurasi GPS:</span>
                    <span>±{{ $attendance->check_in_accuracy ? round($attendance->check_in_accuracy) . 'm' : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Jarak dari Kantor:</span>
                    <strong class="text-emerald-600 dark:text-emerald-400">{{ $attendance->check_in_distance ? $attendance->check_in_distance . ' meter' : '-' }}</strong>
                </div>
            </div>
        </div>

        <!-- Check Out Photo Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm dark:shadow-lg space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="camera" class="w-4 h-4 text-indigo-500 dark:text-indigo-400"></i>
                    <span>Foto Selfie Absen Pulang</span>
                </span>
                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400">
                    {{ $attendance->check_out_at ? $attendance->check_out_at->format('H:i:s') : '-' }} WIB
                </span>
            </div>

            <div class="w-full aspect-square bg-slate-100 dark:bg-slate-950 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 flex items-center justify-center relative">
                @if ($attendance->check_out_photo)
                    <img 
                        src="{{ route('attendance.photo', ['attendance' => $attendance->id, 'type' => 'check_out']) }}" 
                        alt="Foto Pulang" 
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="text-center text-slate-400 dark:text-slate-500 text-xs">
                        <i data-lucide="clock" class="w-8 h-8 mx-auto mb-1 opacity-40"></i>
                        <span>Belum melakukan absen pulang</span>
                    </div>
                @endif
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-850 rounded-xl text-[11px] space-y-1 font-mono text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800">
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Koordinat:</span>
                    <span>{{ $attendance->check_out_latitude ?? '-' }}, {{ $attendance->check_out_longitude ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Akurasi GPS:</span>
                    <span>±{{ $attendance->check_out_accuracy ? round($attendance->check_out_accuracy) . 'm' : '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 dark:text-slate-500">Jarak dari Kantor:</span>
                    <strong class="text-indigo-600 dark:text-indigo-400">{{ $attendance->check_out_distance ? $attendance->check_out_distance . ' meter' : '-' }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Map & Radius Visualization Card -->
    @if ($attendance->check_in_latitude && $attendance->check_in_longitude)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm dark:shadow-lg space-y-3">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="map" class="w-4 h-4 text-brand-500 dark:text-brand-400"></i>
                <span>Visualisasi Peta Lokasi Absensi</span>
            </h3>

            <!-- OpenStreetMap Embed -->
            <div class="w-full h-72 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    scrolling="no" 
                    marginheight="0" 
                    marginwidth="0" 
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $attendance->check_in_longitude - 0.005 }}%2C{{ $attendance->check_in_latitude - 0.005 }}%2C{{ $attendance->check_in_longitude + 0.005 }}%2C{{ $attendance->check_in_latitude + 0.005 }}&amp;layer=mapnik&amp;marker={{ $attendance->check_in_latitude }}%2C{{ $attendance->check_in_longitude }}"
                ></iframe>
            </div>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 text-center">
                Titik penanda menunjukkan lokasi koordinat GPS saat karyawan menekan tombol konfirmasi presensi.
            </p>
        </div>
    @endif

</div>
@endsection
