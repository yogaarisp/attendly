<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Dashboard' }} — Attendly</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        // Init theme immediately to avoid flash
        if (localStorage.getItem('attendly_theme') === 'light') {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Explicit Form Controls Styling for Dark & Light */
        html.dark input, 
        html.dark select, 
        html.dark textarea {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            color-scheme: dark;
        }

        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }

        html.light input, 
        html.light select, 
        html.light textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            color-scheme: light;
        }

        html.light input::placeholder,
        html.light textarea::placeholder {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex selection:bg-brand-500 selection:text-white transition-colors duration-200">

    <!-- Desktop Sidebar -->
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 hidden md:flex sticky top-0 h-screen z-30 transition-colors duration-200">
        <!-- Logo -->
        <div class="h-16 px-6 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20">
                <i data-lucide="scan-face" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-none">Attendly</h1>
                <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Dashboard</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Operasional</div>

            <a href="{{ route('admin.attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.attendance.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="activity" class="w-4 h-4"></i>
                <span>Monitoring Absensi</span>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.reports.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Laporan & Ekspor</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Master Data</div>

            <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.employees.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Data Karyawan</span>
            </a>

            <a href="{{ route('admin.branches.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.branches.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Kantor Cabang</span>
            </a>

            <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.departments.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="building" class="w-4 h-4"></i>
                <span>Departemen</span>
            </a>

            <a href="{{ route('admin.positions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.positions.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="briefcase" class="w-4 h-4"></i>
                <span>Jabatan / Posisi</span>
            </a>

            <div class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sistem</div>

            <a href="{{ route('admin.settings.attendance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="sliders" class="w-4 h-4"></i>
                <span>Pengaturan Jam Kerja</span>
            </a>

            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('admin.audit-logs.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80' }} transition-colors">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                <span>Audit Trail Log</span>
            </a>
        </nav>

        <!-- Current User Box -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-600 dark:text-brand-400 font-bold text-xs flex items-center justify-center shrink-0">
                    AD
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">Administrator</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navigation Header -->
        <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-6 flex items-center justify-between sticky top-0 z-20 transition-colors duration-200">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleMobileMenu()" class="p-2 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white md:hidden cursor-pointer">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h2 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono hidden sm:inline">{{ date('l, d F Y') }}</span>

                <!-- Theme Toggle Button (Light / Dark) -->
                <button 
                    type="button" 
                    id="theme-toggle-btn"
                    onclick="toggleTheme()" 
                    title="Ganti Tema (Gelap / Terang)"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700 shadow-sm flex items-center gap-1.5 text-xs font-semibold transition-all cursor-pointer"
                >
                    <i id="theme-icon" data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                    <span id="theme-text" class="hidden sm:inline">Terang</span>
                </button>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-6 mt-4 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-4 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Page Body -->
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Drawer Sidebar -->
    <div id="mobile-sidebar" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm md:hidden">
        <div class="w-64 bg-white dark:bg-slate-900 h-full border-r border-slate-200 dark:border-slate-800 p-4 flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center">
                        <i data-lucide="scan-face" class="w-5 h-5"></i>
                    </div>
                    <span class="font-bold text-slate-900 dark:text-white text-sm">Attendly Admin</span>
                </div>
                <button type="button" onclick="toggleMobileMenu()" class="p-1 text-slate-400 cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <nav class="flex-1 py-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-white bg-brand-600">Dashboard</a>
                <a href="{{ route('admin.attendance.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Monitoring Absensi</a>
                <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Laporan & Ekspor</a>
                <a href="{{ route('admin.employees.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Data Karyawan</a>
                <a href="{{ route('admin.branches.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Kantor Cabang</a>
                <a href="{{ route('admin.settings.attendance') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Pengaturan Jam Kerja</a>
                <a href="{{ route('admin.audit-logs.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300">Audit Trail</a>
            </nav>
        </div>
    </div>

    <script>
        function updateThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const icon = document.getElementById('theme-icon');
            const text = document.getElementById('theme-text');
            if (icon && text) {
                if (isDark) {
                    icon.setAttribute('data-lucide', 'sun');
                    icon.className = 'w-4 h-4 text-amber-400';
                    text.textContent = 'Terang';
                } else {
                    icon.setAttribute('data-lucide', 'moon');
                    icon.className = 'w-4 h-4 text-indigo-600';
                    text.textContent = 'Gelap';
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
            updateThemeUI();
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            updateThemeUI();
        });

        function toggleMobileMenu() {
            document.getElementById('mobile-sidebar').classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
