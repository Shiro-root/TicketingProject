<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — Helpdesk Enterprise</title>

    {{-- Same no-flash guard as the authenticated layout — keeps the toggle consistent
         across login/forgot/reset-password pages even before a user is authenticated. --}}
    <script>
        (function () {
            var stored = localStorage.getItem('helpdesk-theme');
            if (stored === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (stored === 'light') {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-surface-soft dark:bg-surface-dark">

    {{-- Sticky top bar: red brand mark left, quiet chrome otherwise — mirrors {component.primary-nav} --}}
    <header class="h-16 flex items-center justify-between px-lg md:px-xl border-b border-hairline bg-canvas dark:bg-surface-dark dark:border-white/10">
        <div class="flex items-center min-w-0">
            <span class="text-heading-md text-primary">Helpdesk</span>
            <span class="hidden sm:inline text-body-sm text-mute ml-sm">Enterprise</span>
        </div>

        <button
            type="button"
            class="btn-icon"
            onclick="window.helpdeskToggleTheme()"
            title="Ganti tema terang/gelap"
            aria-label="Ganti tema terang/gelap"
        >
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>
    </header>

    <main class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-lg py-xxl">
        <div class="w-full flex flex-col items-center gap-lg">
            @yield('content')
        </div>
    </main>

    <script>
        window.helpdeskToggleTheme = function () {
            var root = document.documentElement;
            var isDark = root.classList.toggle('dark');
            localStorage.setItem('helpdesk-theme', isDark ? 'dark' : 'light');
        };
    </script>
</body>
</html>
