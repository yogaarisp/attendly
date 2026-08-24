@extends('layouts.admin', ['title' => 'Tambah Kantor Cabang'])

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Tambah Kantor Cabang Baru</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Tentukan titik koordinat kantor dan batas radius absensi</p>
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
        <form method="POST" action="{{ route('admin.branches.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode Cabang <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', 'BR-'.rand(10, 99)) }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kantor Cabang <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Kantor Cabang Bandung" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Lengkap</label>
                    <textarea name="address" rows="2" placeholder="Jl. Nama Jalan No. XX, Kota..." class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="022-1234567" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Zona Waktu (Timezone) <span class="text-rose-500">*</span></label>
                    <select name="timezone" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="Asia/Jakarta" {{ old('timezone') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                        <option value="Asia/Makassar" {{ old('timezone') == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                        <option value="Asia/Jayapura" {{ old('timezone') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
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
                            <input type="text" id="latitude_input" name="latitude" value="{{ old('latitude', '-7.79560000') }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Longitude</label>
                            <input type="text" id="longitude_input" name="longitude" value="{{ old('longitude', '110.36950000') }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Radius (Meter)</label>
                            <input type="number" name="radius_meter" value="{{ old('radius_meter', 100) }}" min="10" max="5000" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                        </div>
                    </div>
                </div>

                <!-- Work Hours Config -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Masuk Standar <span class="text-rose-500">*</span></label>
                    <input type="time" name="work_start_time" value="{{ old('work_start_time', '08:00') }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jam Pulang Standar <span class="text-rose-500">*</span></label>
                    <input type="time" name="work_end_time" value="{{ old('work_end_time', '17:00') }}" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span></label>
                    <input type="number" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', 15) }}" min="0" max="120" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Batas Minimal Akurasi GPS (Meter) <span class="text-rose-500">*</span></label>
                    <input type="number" name="minimum_gps_accuracy" value="{{ old('minimum_gps_accuracy', 100) }}" min="10" max="500" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Cabang <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.branches.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/25 transition-all cursor-pointer">
                    Simpan Kantor Cabang
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function detectCurrentLocation() {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung Geolocation.');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('latitude_input').value = pos.coords.latitude.toFixed(8);
                document.getElementById('longitude_input').value = pos.coords.longitude.toFixed(8);
                alert(`Lokasi berhasil dideteksi: ${pos.coords.latitude.toFixed(6)}, ${pos.coords.longitude.toFixed(6)}`);
            },
            (err) => {
                alert('Gagal mendeteksi lokasi: ' + err.message);
            },
            { enableHighAccuracy: true }
        );
    }
</script>
@endpush
