@extends('layouts.employee', ['title' => 'Profil Karyawan'])

@section('content')
<div class="space-y-4">
    
    <!-- Profile Card -->
    <div class="bg-gradient-to-br from-brand-600 to-indigo-700 dark:from-slate-800 dark:to-slate-850 border border-brand-500/30 dark:border-slate-700/60 rounded-3xl p-6 shadow-md dark:shadow-xl text-center relative overflow-hidden">
        <div class="w-20 h-20 rounded-full bg-white/20 dark:bg-gradient-to-tr dark:from-brand-600 dark:to-indigo-500 border-4 border-white/30 dark:border-slate-700 mx-auto flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-brand-500/30">
            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
        </div>
        <h2 class="text-base font-bold text-white tracking-tight mt-3">{{ $employee->full_name }}</h2>
        <p class="text-xs text-brand-100 dark:text-brand-400 font-semibold mt-0.5">{{ $employee->employee_code }}</p>
        <span class="inline-block mt-2 px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $employee->status === 'active' ? 'bg-white/20 text-white' : 'bg-rose-500/30 text-rose-100' }}">
            {{ $employee->status === 'active' ? 'Karyawan Aktif' : 'Non-Aktif' }}
        </span>
    </div>

    <!-- Personal & Employment Details -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-5 shadow-sm dark:shadow-xl space-y-3.5 text-xs">
        <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Informasi Karyawan</h3>
        
        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-700/40">
            <span class="text-slate-500 dark:text-slate-400">Email Akun</span>
            <span class="font-semibold text-slate-900 dark:text-white font-mono">{{ $employee->email }}</span>
        </div>

        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-700/40">
            <span class="text-slate-500 dark:text-slate-400">Nomor Telepon</span>
            <span class="font-semibold text-slate-900 dark:text-white font-mono">{{ $employee->phone ?? '-' }}</span>
        </div>

        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-700/40">
            <span class="text-slate-500 dark:text-slate-400">Departemen</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->department->name ?? '-' }}</span>
        </div>

        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-700/40">
            <span class="text-slate-500 dark:text-slate-400">Posisi / Jabatan</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->position->name ?? '-' }}</span>
        </div>

        <div class="flex justify-between items-center py-1.5 border-b border-slate-100 dark:border-slate-700/40">
            <span class="text-slate-500 dark:text-slate-400">Kantor Cabang</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->branch->name ?? '-' }}</span>
        </div>

        <div class="flex justify-between items-center py-1.5">
            <span class="text-slate-500 dark:text-slate-400">Tanggal Bergabung</span>
            <span class="font-semibold text-slate-900 dark:text-white">{{ $employee->join_date->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <!-- Schedule Info -->
    <div class="bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 rounded-3xl p-5 shadow-sm dark:shadow-xl space-y-3 text-xs">
        <h3 class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-1">Jadwal & Ketentuan Absensi</h3>
        <p class="text-slate-500 dark:text-slate-400">
            Jam Masuk: <strong class="text-slate-800 dark:text-slate-200">{{ substr($employee->branch->attendanceSetting->work_start_time ?? '08:00', 0, 5) }}</strong> (Toleransi {{ $employee->branch->attendanceSetting->late_tolerance_minutes ?? 15 }} mnt)
        </p>
        <p class="text-slate-500 dark:text-slate-400">
            Jam Pulang: <strong class="text-slate-800 dark:text-slate-200">{{ substr($employee->branch->attendanceSetting->work_end_time ?? '17:00', 0, 5) }}</strong>
        </p>
        <p class="text-slate-500 dark:text-slate-400">
            Maksimum Radius: <strong class="text-slate-800 dark:text-slate-200">{{ $employee->branch->radius_meter ?? 100 }} meter</strong> dari lokasi kantor.
        </p>
    </div>

    <!-- Security Notice -->
    <p class="text-center text-[10px] text-slate-400 dark:text-slate-500 px-4">
        Perubahan data sensitif karyawan hanya dapat dilakukan melalui Administrator HR.
    </p>

</div>
@endsection
