@extends('layouts.admin', ['title' => 'Edit Kantor Cabang'])

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Edit Kantor Cabang: {{ $branch->name }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui koordinat, radius presensi, dan jadwal absensi</p>
        </div>
        <a href="{{ route('admin.branches.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-600 dark:text-rose-400 text-xs">
            <p class="font-bold mb-1">Terdapat kesalahan input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-xl transition-colors">
        <form method="POST" action="{{ route('admin.branches.update', $branch->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Cabang <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kantor Cabang <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">Alamat Lengkap</label>
                        <button type="button" id="address_search_btn" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg text-[11px] font-semibold transition-colors">
                            <i data-lucide="search" class="w-3 h-3"></i>
                            <span>Cari di Peta</span>
                        </button>
                    </div>
                    <textarea name="address" id="address_input" rows="2" placeholder="Tulis alamat (contoh: Jl Sudirman No 25, Jakarta) lalu tekan Enter atau klik Cari di Peta — koordinat & peta otomatis mengikuti..." class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">{{ old('address', $branch->address) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Zona Waktu (Timezone) <span class="text-rose-500">*</span></label>
                    <select name="timezone" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="Asia/Jakarta" {{ old('timezone', $branch->timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                        <option value="Asia/Makassar" {{ old('timezone', $branch->timezone) == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                        <option value="Asia/Jayapura" {{ old('timezone', $branch->timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                    </select>
                </div>

                <!-- GPS Coordinates with helper -->
                <div class="sm:col-span-2 p-4 bg-slate-50 dark:bg-slate-850/80 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-brand-500 dark:text-brand-400"></i>
                            <span>Titik Koordinat & Batas Radius Absensi</span>
                        </span>
                        <button type="button" onclick="detectCurrentLocation()" class="px-2.5 py-1 bg-brand-600/20 hover:bg-brand-600/30 text-brand-600 dark:text-brand-300 border border-brand-500/30 rounded-lg text-[11px] font-semibold flex items-center gap-1 transition-colors">
                            <i data-lucide="crosshair" class="w-3 h-3"></i>
                            <span>Ambil Lokasi Saya</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Latitude</label>
                            <input type="text" id="latitude_input" name="latitude" value="{{ old('latitude', $branch->latitude) }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Longitude</label>
                            <input type="text" id="longitude_input" name="longitude" value="{{ old('longitude', $branch->longitude) }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Radius (Meter)</label>
                            <input type="number" name="radius_meter" value="{{ old('radius_meter', $branch->radius_meter) }}" min="10" max="5000" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                    </div>

                    <!-- Peta interaktif: klik/geser pin untuk atur titik + alamat otomatis -->
                    <div id="branch_map" class="h-64 w-full rounded-xl border border-slate-200 dark:border-slate-700 z-0 overflow-hidden"></div>
                    <div class="flex items-start gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                        <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 shrink-0 text-brand-500"></i>
                        <span id="address_preview" class="italic">Klik peta atau geser pin untuk mengatur titik — alamat lengkap akan terisi otomatis.</span>
                    </div>
                </div>

                <!-- Work Hours Config -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Masuk Standar <span class="text-rose-500">*</span></label>
                    <input type="time" name="work_start_time" value="{{ old('work_start_time', substr($branch->attendanceSetting->work_start_time ?? '08:00', 0, 5)) }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Pulang Standar <span class="text-rose-500">*</span></label>
                    <input type="time" name="work_end_time" value="{{ old('work_end_time', substr($branch->attendanceSetting->work_end_time ?? '17:00', 0, 5)) }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', $branch->attendanceSetting->late_tolerance_minutes ?? 15) }}" min="0" max="120" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Batas Minimal Akurasi GPS (Meter) <span class="text-rose-500">*</span></label>
                    <input type="number" name="minimum_gps_accuracy" value="{{ old('minimum_gps_accuracy', $branch->attendanceSetting->minimum_gps_accuracy ?? 100) }}" min="10" max="500" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sistem Absensi Cabang Ini</label>
                    <select name="attendance_enabled" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="1" {{ old('attendance_enabled', $branch->attendanceSetting->attendance_enabled ?? true) ? 'selected' : '' }}>Aktif (Diizinkan Absensi)</option>
                        <option value="0" {{ !old('attendance_enabled', $branch->attendanceSetting->attendance_enabled ?? true) ? 'selected' : '' }}>Dinonaktifkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Cabang <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="active" {{ old('status', $branch->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $branch->status) == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.branches.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/25 transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="{{ asset('js/branch-location-picker.js') }}"></script>
@endpush
