@extends('layouts.admin', ['title' => 'Kantor Cabang'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Daftar Kantor Cabang</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Atur lokasi kantor, koordinat GPS, radius presensi, dan zona waktu</p>
        </div>
        <a href="{{ route('admin.branches.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="map-pin" class="w-4 h-4"></i>
            <span>Tambah Cabang</span>
        </a>
    </div>

    <!-- Branch Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($branches as $branch)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-5 shadow-sm dark:shadow-lg relative flex flex-col justify-between transition-colors">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-brand-600 dark:text-brand-400 uppercase bg-brand-500/10 px-2 py-0.5 rounded-md">
                                {{ $branch->code }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight mt-1.5">{{ $branch->name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $branch->address ?? 'Alamat belum diatur' }}</p>
                        </div>
                        <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $branch->status === 'active' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/20 text-rose-700 dark:text-rose-300' }}">
                            {{ $branch->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>

                    <!-- Geolocation & Schedule Pill -->
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Koordinat GPS</span>
                            <span class="font-mono text-slate-800 dark:text-slate-200 text-[11px] mt-0.5 block truncate">
                                {{ $branch->latitude }}, {{ $branch->longitude }}
                            </span>
                        </div>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Radius Absensi</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 block">
                                {{ $branch->radius_meter }} Meter
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 p-2.5 bg-slate-50 dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-800 text-xs flex items-center justify-between text-slate-500 dark:text-slate-400">
                        <span>Jam Masuk: <strong class="text-slate-800 dark:text-white">{{ substr($branch->attendanceSetting->work_start_time ?? '08:00', 0, 5) }}</strong></span>
                        <span>Jam Pulang: <strong class="text-slate-800 dark:text-white">{{ substr($branch->attendanceSetting->work_end_time ?? '17:00', 0, 5) }}</strong></span>
                        <span>Timezone: <strong class="text-brand-600 dark:text-brand-400">{{ $branch->timezone }}</strong></span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ $branch->employees_count }} Karyawan Terdaftar
                    </span>
                    <a href="{{ route('admin.branches.edit', $branch->id) }}" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit & Atur Lokasi</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
