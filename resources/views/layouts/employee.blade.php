<!DOCTYPE html>
<html lang="id" class="light h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#4f46e5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <title>{{ $title ?? 'Karyawan' }} — Attendly</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            850: '#151f32',
                            950: '#0b1120',
                        },
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('attendly_theme') === 'dark') {
            document.documentElement.classList.remove('light');
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        html.dark input, 
        html.dark select, 
        html.dark textarea {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            color-scheme: dark;
        }

        html.light input, 
        html.light select, 
        html.light textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            color-scheme: light;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col items-center justify-between selection:bg-brand-500 selection:text-white transition-colors duration-200">

    <!-- Offline Alert Banner (PRD section 48) -->
    <div id="offline-banner" class="hidden fixed top-0 inset-x-0 z-50 bg-rose-600 text-white text-xs font-semibold py-2.5 px-4 text-center shadow-lg flex items-center justify-center gap-2">
        <i data-lucide="wifi-off" class="w-4 h-4"></i>
        <span>Anda sedang offline. Absensi membutuhkan koneksi internet aktif.</span>
    </div>

    <!-- Main Container (App Shell) -->
    <div class="w-full max-w-lg min-h-screen flex flex-col bg-white dark:bg-slate-900 border-x border-slate-200 dark:border-slate-800 shadow-2xl relative pb-24 transition-colors duration-200">
        
        <!-- Top App Bar -->
        <header class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800/80 px-4 py-3.5 flex items-center justify-between transition-colors duration-200">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                    <i data-lucide="scan-face" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight leading-none">Attendly</h1>
                    <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ auth()->user()->employee->branch->name ?? 'Kantor Pusat' }}</p>
                </div>
            </div>

            <!-- Theme Toggle, Profile & Logout -->
            <div class="flex items-center gap-2">
                <!-- Theme Switcher -->
                <button 
                    type="button" 
                    onclick="toggleTheme()" 
                    title="Ganti Tema (Gelap / Terang)"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-center transition-all cursor-pointer"
                >
                    <i id="emp-theme-icon" data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                </button>

                <a href="{{ route('employee.profile') }}" class="flex items-center gap-2 p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    <div class="w-8 h-8 rounded-full bg-brand-500/20 border border-brand-500/40 flex items-center justify-center text-brand-600 dark:text-brand-300 font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-4 mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-4 mt-3 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="mx-4 mt-3 p-3 bg-sky-500/10 border border-sky-500/30 rounded-xl text-sky-600 dark:text-sky-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Content Body -->
        <main class="flex-1 px-4 py-4">
            @yield('content')
        </main>

        <!-- Bottom Navigation Bar (Mobile PWA) -->
        <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800/80 max-w-lg mx-auto transition-colors duration-200">
            <div class="grid grid-cols-4 h-16 items-center px-2">
                
                <!-- Dashboard -->
                <a href="{{ route('employee.dashboard') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('employee.dashboard') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }} transition-colors">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    <span class="text-[10px] font-semibold">Beranda</span>
                </a>

                <!-- Absen (Smart Dynamic Button) -->
                <a href="{{ route('employee.attendance.checkin') }}" class="flex flex-col items-center justify-center gap-1 relative -top-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/40 border-2 border-white dark:border-slate-900 active:scale-95 transition-transform">
                        <i data-lucide="camera" class="w-6 h-6"></i>
                    </div>
                    <span class="text-[10px] font-bold text-brand-600 dark:text-brand-400">Absen</span>
                </a>

                <!-- Riwayat -->
                <a href="{{ route('employee.history') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('employee.history') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }} transition-colors">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span class="text-[10px] font-semibold">Riwayat</span>
                </a>

                <!-- Profil -->
                <a href="{{ route('employee.profile') }}" class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('employee.profile') ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200' }} transition-colors">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    <span class="text-[10px] font-semibold">Profil</span>
                </a>

            </div>
        </nav>

    </div>

    <script>
        function updateEmpThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const icon = document.getElementById('emp-theme-icon');
            if (icon) {
                if (isDark) {
                    icon.setAttribute('data-lucide', 'sun');
                    icon.className = 'w-4 h-4 text-amber-400';
                } else {
                    icon.setAttribute('data-lucide', 'moon');
                    icon.className = 'w-4 h-4 text-indigo-600';
                }
                lucide.createIcons();
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('attendly_theme', 'light');
            } else {
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark');
                localStorage.setItem('attendly_theme', 'dark');
            }
            updateEmpThemeUI();
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            updateEmpThemeUI();
        });

        function updateOnlineStatus() {
            const banner = document.getElementById('offline-banner');
            if (!navigator.onLine) {
                banner.classList.remove('hidden');
            } else {
                banner.classList.add('hidden');
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    </script>
    @stack('scripts')
</body>
</html>
