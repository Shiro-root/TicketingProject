<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Helpdesk Enterprise</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-surface-soft dark:bg-surface-dark text-body dark:text-on-dark">

    {{-- {component.primary-nav} adapted for the authenticated shell --}}
    <header
        class="h-16 flex items-center justify-between px-xl border-b border-hairline bg-canvas dark:bg-surface-dark dark:border-white/10 sticky top-0 z-20">
        <div class="flex items-center gap-xl">
            <a href="{{ route('dashboard') }}" class="text-heading-md text-primary">Helpdesk</a>
        </div>

        <div class="flex items-center gap-md">
            <button type="button" onclick="document.getElementById('shortcut-help-modal').classList.remove('hidden')"
                class="hidden md:flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-card dark:hover:bg-white/5"
                title="Keyboard Shortcut (?)">
                <span class="text-heading-md">⌨️</span>
            </button>

            <a href="{{ route('notifications.index') }}"
                class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-card dark:hover:bg-white/5"
                title="Notifikasi">
                <span class="text-heading-md">🔔</span>
                @php($unread = auth()->user()->unreadNotifications()->count())
                @if($unread)
                    <span
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-xxs rounded-full bg-primary text-white text-[10px] leading-[18px] text-center font-semibold">
                        {{ $unread > 9 ? '9+' : $unread }}
                    </span>
                @endif
            </a>
            <details class="relative">
                <summary
                    class="list-none flex items-center gap-sm cursor-pointer select-none px-sm py-xs rounded-full hover:bg-surface-card dark:hover:bg-white/5">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}"
                            class="w-8 h-8 rounded-full object-cover">
                    @else
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-caption-md font-semibold">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                    <span class="text-body-strong text-ink dark:text-on-dark">{{ auth()->user()->name }}</span>
                </summary>

                <div
                    class="absolute right-0 mt-sm w-56 bg-canvas dark:bg-surface-dark border border-hairline dark:border-white/10 rounded-md shadow-modal py-xs z-30">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-md py-sm text-body-sm text-ink dark:text-on-dark hover:bg-surface-card dark:hover:bg-white/5">
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-md py-sm text-body-sm text-error hover:bg-surface-card dark:hover:bg-white/5">
                            Keluar
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <div class="flex">
        {{-- Sidebar: surface-card chrome, role-aware nav. --}}
        <aside
            class="hidden md:flex flex-col w-64 shrink-0 min-h-[calc(100vh-4rem)] bg-surface-card dark:bg-black/20 border-r border-hairline dark:border-white/10 px-md py-lg gap-xxs">
            <a href="{{ route('dashboard') }}"
                class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('dashboard') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                Dashboard
            </a>

            {{-- Modul 4: Ticket Management --}}
            <a href="{{ route('tickets.index') }}"
                class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('tickets.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                Tickets
            </a>

            @can('viewAny', \App\Models\KnowledgeBaseArticle::class)
                <a href="{{ route('knowledge-base.index') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('knowledge-base.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Knowledge Base
                </a>
            @endcan

            @can('viewAny', \App\Models\Asset::class)
                <a href="{{ route('assets.index') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('assets.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Assets
                </a>
            @endcan

            {{-- Modul 8: Report --}}
            @if(auth()->user()->hasPermission('report.view'))
                <a href="{{ route('reports.index') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('reports.index') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Reports
                </a>
            @endif

            {{-- Modul 9D: Scheduled Report --}}
            @if(auth()->user()->hasPermission('report.export'))
                <a href="{{ route('report-schedules.index') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('report-schedules.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Laporan Terjadwal
                </a>
            @endif

            {{-- Modul 9A/9D: Admin (Pengumuman, Maintenance Mode) --}}
            @if(auth()->user()->hasPermission('settings.manage'))
                <span class="mt-md mb-xxs px-md text-caption-md text-ash uppercase tracking-wide">Admin</span>
                <a href="{{ route('announcements.index') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('announcements.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Pengumuman
                </a>
                <a href="{{ route('settings.maintenance') }}"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('settings.maintenance*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/5' }}">
                    Maintenance Mode
                </a>
            @endif
        </aside>

        <main class="flex-1 px-xl py-xl">
            <x-announcement-banner />
            @include('partials.flash-messages')
            @yield('content')
        </main>
    </div>

    <x-keyboard-shortcuts />

    @stack('scripts')
</body>

</html>
