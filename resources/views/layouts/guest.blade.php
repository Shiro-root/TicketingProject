<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'light') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Masuk' }} — Helpdesk Enterprise</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-surface-soft dark:bg-surface-dark">

    {{-- Sticky top bar: red brand mark left, quiet chrome otherwise — mirrors {component.primary-nav} --}}
    <header class="h-16 flex items-center px-xl border-b border-hairline bg-canvas dark:bg-surface-dark dark:border-white/10">
        <span class="text-heading-md text-primary">Helpdesk</span>
        <span class="text-body-sm text-mute ml-sm">Enterprise</span>
    </header>

    <main class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-lg py-xxl">
        <div class="w-full flex flex-col items-center gap-lg">
            @yield('content')
        </div>
    </main>
</body>
</html>
