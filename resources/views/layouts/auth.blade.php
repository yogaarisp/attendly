<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <title>{{ $title ?? 'Login' }} — Attendly</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
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
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col justify-center items-center p-4 relative overflow-x-hidden selection:bg-brand-500 selection:text-white transition-colors duration-200">
    
    <!-- Top Theme Switcher -->
    <div class="fixed top-4 right-4 z-50">
        <button 
            type="button" 
            onclick="toggleTheme()" 
            title="Ganti Tema"
            class="p-2.5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 shadow-md flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 transition-all cursor-pointer"
        >
            <i id="auth-theme-icon" data-lucide="sun" class="w-4 h-4 text-amber-500"></i>
            <span id="auth-theme-text">Terang</span>
        </button>
    </div>

    <!-- Ambient Background Lighting -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-brand-600/15 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <main class="w-full max-w-md">
        @yield('content')
    </main>

    <footer class="mt-8 text-center text-xs text-slate-400 dark:text-slate-500">
        &copy; {{ date('Y') }} Attendly — Smart Employee Attendance System.
    </footer>

    <script>
        function updateAuthThemeUI() {
            const isDark = document.documentElement.classList.contains('dark');
            const icon = document.getElementById('auth-theme-icon');
            const text = document.getElementById('auth-theme-text');
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
            updateAuthThemeUI();
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            updateAuthThemeUI();
        });
    </script>
    @stack('scripts')
</body>
</html>
