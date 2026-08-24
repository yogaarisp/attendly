<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Dashboard' }} — Attendly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        slate: { 850: '#151f32', 950: '#0b1120' },
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe',
                            300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1',
                            600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81',
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

        html.dark input, html.dark select, html.dark textarea {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            color-scheme: dark;
        }
        html.dark input::placeholder, html.dark textarea::placeholder { color: #64748b !important; }

        html.light input, html.light select, html.light textarea {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
            color-scheme: light;
        }
        html.light input::placeholder, html.light textarea::placeholder { color: #94a3b8 !important; }

        /* Sidebar nav scroll tanpa scrollbar terlihat */
        #sidebar-nav { overflow-y: auto; scrollbar-width: none; }
        #sidebar-nav::-webkit-scrollbar { display: none; }

        /* Mobile drawer transition */
        #mobile-overlay { transition: opacity 0.25s ease; }
        #mobile-drawer  { transition: transform 0.25s ease; }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex selection:bg-brand-500 selection:text-white transition-colors duration-200">

    {{-- ═══════════════════════════════════════════════════════════
         DESKTOP SIDEBAR (hidden di mobile, tampil mulai md:)
    ═══════════════════════════════════════════════════════════ --}}
    <aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800
                  hidden md:flex flex-col shrink-0 sticky top-0 h-screen z-30 transition-colors duration-200">

        {{-- Logo --}}
        <div class="h-16 px-5 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3 shrink-0">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-brand-500/20 shrink-0">
                <i data-lucide="scan-face" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight leading-none">Attendly</h1>
                <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">Admin Panel</span>
            </div>
        </div>

        {{-- Nav — flex-1 + overflow agar tidak menutupi user box --}}
        <nav id="sidebar-nav" class="flex-1 px-4 py-4 space-y-0.5 min-h-0">

            @php
            $navLink = fn($route, $icon, $label, $pattern) =>
                '<a href="'.route($route).'" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold '.
                (request()->routeIs($pattern)
                    ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30'
                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80').
                ' transition-colors"><i data-lucide="'.$icon.'" class="w-4 h-4 shrink-0"></i><span>'.$label.'</span></a>';
            @endphp

            {!! $navLink('admin.dashboard', 'layout-dashboard', 'Dashboard', 'admin.dashboard') !!}

            <p class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Operasional</p>
            {!! $navLink('admin.attendance.index', 'activity', 'Monitoring Absensi', 'admin.attendance.*') !!}
            {!! $navLink('admin.reports.index', 'file-spreadsheet', 'Laporan & Ekspor', 'admin.reports.*') !!}

            <p class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Master Data</p>
            {!! $navLink('admin.employees.index', 'users', 'Data Karyawan', 'admin.employees.*') !!}
            {!! $navLink('admin.branches.index', 'map-pin', 'Kantor Cabang', 'admin.branches.*') !!}
            {!! $navLink('admin.departments.index', 'building', 'Departemen', 'admin.departments.*') !!}
            {!! $navLink('admin.positions.index', 'briefcase', 'Jabatan / Posisi', 'admin.positions.*') !!}

            <p class="pt-4 pb-1 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sistem</p>
            {!! $navLink('admin.settings.attendance', 'sliders', 'Pengaturan Jam Kerja', 'admin.settings.*') !!}
            {!! $navLink('admin.audit-logs.index', 'shield-alert', 'Audit Trail Log', 'admin.audit-logs.*') !!}
        </nav>

        {{-- User Box — shrink-0 agar SELALU tampil, tidak pernah terdorong --}}
        <div class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-600 dark:text-brand-400 font-bold text-sm flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout"
                        class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer"
                    >
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ═══════════════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">

        {{-- Top Header --}}
        <header class="h-14 md:h-16 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md
                       border-b border-slate-200 dark:border-slate-800
                       px-4 md:px-6 flex items-center justify-between
                       sticky top-0 z-20 transition-colors duration-200">

            <div class="flex items-center gap-3">
                {{-- Hamburger — hanya tampil di mobile --}}
                <button type="button" onclick="openMobileMenu()"
                    class="md:hidden p-2 -ml-1 rounded-xl text-slate-600 dark:text-slate-400
                           hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800
                           transition-colors cursor-pointer">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                {{-- Logo mini di mobile --}}
                <div class="flex items-center gap-2 md:hidden">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white">
                        <i data-lucide="scan-face" class="w-4 h-4"></i>
                    </div>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">Attendly</span>
                </div>

                <h2 class="hidden md:block text-base font-bold text-slate-900 dark:text-white tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400 font-mono hidden lg:inline">{{ date('l, d F Y') }}</span>

                {{-- Theme toggle --}}
                <button type="button" id="theme-toggle-btn" onclick="toggleTheme()" title="Ganti Tema"
                    class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300
                           hover:text-brand-600 dark:hover:text-brand-400 border border-slate-200 dark:border-slate-700
                           shadow-sm flex items-center gap-1.5 text-xs font-semibold transition-all cursor-pointer">
                    <i id="theme-icon" data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
                    <span id="theme-text" class="hidden sm:inline">Terang</span>
                </button>

                {{-- Avatar + nama di mobile (bukan logout, itu di drawer) --}}
                <button type="button" onclick="openMobileMenu()"
                    class="md:hidden flex items-center gap-1.5 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-600 dark:text-brand-400 font-bold text-xs flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </button>
            </div>
        </header>

        {{-- Page title bar — hanya di mobile --}}
        <div class="md:hidden px-4 pt-3 pb-1">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mx-4 md:mx-6 mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-600 dark:text-emerald-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="mx-4 md:mx-6 mt-3 p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-600 dark:text-rose-400 text-xs font-medium flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 p-4 md:p-6 pb-6">
            @yield('content')
        </main>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MOBILE DRAWER SIDEBAR
         - Full overlay + slide-in drawer dari kiri
         - Scroll-safe: user box & logout SELALU di bawah
    ═══════════════════════════════════════════════════════════ --}}
    {{-- Overlay --}}
    <div id="mobile-overlay"
        onclick="closeMobileMenu()"
        class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden hidden opacity-0">
    </div>

    {{-- Drawer --}}
    <div id="mobile-drawer"
        class="fixed top-0 left-0 bottom-0 z-50 w-72 md:hidden
               bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800
               flex flex-col shadow-2xl -translate-x-full">

        {{-- Drawer Header --}}
        <div class="h-14 px-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white shadow-md">
                    <i data-lucide="scan-face" class="w-4 h-4"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 dark:text-white leading-none">Attendly</h1>
                    <span class="text-[10px] font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider">Admin Panel</span>
                </div>
            </div>
            <button type="button" onclick="closeMobileMenu()"
                class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Drawer Nav — flex-1 + overflow scroll --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto min-h-0" style="scrollbar-width:none">

            @php
            $mobileLink = function($route, $icon, $label, $pattern) {
                $active = request()->routeIs($pattern);
                return '<a href="'.route($route).'" onclick="closeMobileMenu()" 
                    class="flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-semibold '.
                    ($active
                        ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30'
                        : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800').
                    ' transition-colors">
                    <i data-lucide="'.$icon.'" class="w-4 h-4 shrink-0"></i>
                    <span>'.$label.'</span>
                    </a>';
            };
            @endphp

            {!! $mobileLink('admin.dashboard', 'layout-dashboard', 'Dashboard', 'admin.dashboard') !!}

            <p class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Operasional</p>
            {!! $mobileLink('admin.attendance.index', 'activity', 'Monitoring Absensi', 'admin.attendance.*') !!}
            {!! $mobileLink('admin.reports.index', 'file-spreadsheet', 'Laporan & Ekspor', 'admin.reports.*') !!}

            <p class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Master Data</p>
            {!! $mobileLink('admin.employees.index', 'users', 'Data Karyawan', 'admin.employees.*') !!}
            {!! $mobileLink('admin.branches.index', 'map-pin', 'Kantor Cabang', 'admin.branches.*') !!}
            {!! $mobileLink('admin.departments.index', 'building', 'Departemen', 'admin.departments.*') !!}
            {!! $mobileLink('admin.positions.index', 'briefcase', 'Jabatan / Posisi', 'admin.positions.*') !!}

            <p class="pt-4 pb-1.5 px-3 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sistem</p>
            {!! $mobileLink('admin.settings.attendance', 'sliders', 'Pengaturan Jam Kerja', 'admin.settings.*') !!}
            {!! $mobileLink('admin.audit-logs.index', 'shield-alert', 'Audit Trail Log', 'admin.audit-logs.*') !!}
        </nav>

        {{-- Drawer User Box — shrink-0, SELALU di bawah, tidak bisa tertimpa --}}
        <div class="shrink-0 p-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            {{-- Info user --}}
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-brand-500/20 border border-brand-500/30 text-brand-600 dark:text-brand-400 font-bold text-sm flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Administrator</p>
                </div>
            </div>
            {{-- Logout button — full width, tidak bisa tersembunyi --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                           bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-100 dark:hover:bg-rose-500/20
                           text-rose-600 dark:text-rose-400 text-xs font-bold
                           border border-rose-200 dark:border-rose-500/30
                           transition-colors cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar / Logout</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        // ── Theme ─────────────────────────────────────────────────
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

        // ── Mobile Drawer ──────────────────────────────────────────
        const overlay = document.getElementById('mobile-overlay');
        const drawer  = document.getElementById('mobile-drawer');

        function openMobileMenu() {
            overlay.classList.remove('hidden');
            // Trigger transition setelah display:block aktif
            requestAnimationFrame(() => {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
                drawer.classList.remove('-translate-x-full');
                drawer.classList.add('translate-x-0');
            });
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            drawer.classList.remove('translate-x-0');
            drawer.classList.add('-translate-x-full');
            document.body.style.overflow = '';
            // Sembunyikan overlay setelah animasi selesai
            setTimeout(() => overlay.classList.add('hidden'), 260);
        }

        // Tutup drawer jika tekan Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeMobileMenu();
        });

        // ── Init ──────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            updateThemeUI();
        });
    </script>
    @stack('scripts')
</body>
</html>
