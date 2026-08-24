@extends('layouts.auth', ['title' => 'Masuk ke Akun'])

@section('content')
<div class="bg-white dark:bg-slate-800/80 backdrop-blur-xl border border-slate-200 dark:border-slate-700/60 rounded-3xl p-8 shadow-lg dark:shadow-2xl dark:shadow-black/40">
    <!-- Brand Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 shadow-lg shadow-brand-500/30 mb-4 text-white">
            <i data-lucide="scan-face" class="w-8 h-8"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Attendly</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Smart Employee Attendance System</p>
    </div>

    @if (session('status'))
        <div class="mb-5 p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-600 dark:text-rose-400 text-xs font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Email Karyawan / Admin</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email', 'admin@example.com') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="nama@perusahaan.com"
                    class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                >
            </div>
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    value="password"
                    required 
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700/80 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                >
            </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/80 text-brand-600 focus:ring-brand-500/30">
                <span>Ingat saya</span>
            </label>
            <span class="text-slate-400 dark:text-slate-500 text-xs">Aman & Terenkripsi</span>
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full mt-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-brand-500/25 transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer"
        >
            <span>Masuk Sekarang</span>
            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
        </button>
    </form>

    <!-- Quick Demo Logins -->
    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700/50 text-center">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Akun Demo (Development)</p>
        <div class="grid grid-cols-2 gap-2">
            <button 
                type="button" 
                onclick="fillCredentials('admin@example.com', 'password')"
                class="px-3 py-2 bg-slate-100 dark:bg-slate-900/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center justify-center gap-1.5"
            >
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-brand-500 dark:text-brand-400"></i>
                <span>Admin Demo</span>
            </button>
            <button 
                type="button" 
                onclick="fillCredentials('employee@example.com', 'password')"
                class="px-3 py-2 bg-slate-100 dark:bg-slate-900/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center justify-center gap-1.5"
            >
                <i data-lucide="user" class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400"></i>
                <span>Karyawan Demo</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endpush
