<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — Helpdesk Enterprise</title>

    {{--
        Prevents a "flash of wrong theme": runs before Tailwind's compiled CSS paints,
        so if the visitor previously toggled the in-header theme switch we honor that
        immediately instead of waiting for the page (and Livewire/Alpine) to boot.
        Falls back to the server-rendered class above (from users.theme) otherwise.
    --}}
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

<body class="min-h-screen bg-surface-soft dark:bg-surface-dark text-body dark:text-on-dark"
      x-data="{ sidebarOpen: false }">

    {{-- {component.primary-nav} adapted for the authenticated shell, now responsive --}}
    <header
        class="h-16 flex items-center justify-between px-lg md:px-xl border-b border-hairline bg-canvas dark:bg-surface-dark dark:border-white/10 sticky top-0 z-40">
        <div class="flex items-center gap-sm md:gap-xl min-w-0">
            {{-- Hamburger — only shown below md, opens the off-canvas sidebar --}}
            <button
                type="button"
                class="md:hidden shrink-0 flex items-center justify-center w-10 h-10 rounded-md hover:bg-surface-card dark:hover:bg-white/10"
                @click="sidebarOpen = !sidebarOpen"
                :aria-expanded="sidebarOpen.toString()"
                aria-label="Buka menu navigasi"
            >
                <svg x-show="!sidebarOpen" class="w-5 h-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round">
                    <path d="M3 5h14M3 10h14M3 15h14"/>
                </svg>
                <svg x-show="sidebarOpen" x-cloak class="w-5 h-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round">
                    <path d="M5 5l10 10M15 5L5 15"/>
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="text-heading-md text-primary shrink-0">Helpdesk</a>
        </div>

        <div class="flex items-center gap-xs md:gap-md">
            {{-- Light/dark toggle: instant client-side switch, no page reload required --}}
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

            <a href="{{ route('notifications.index') }}"
                class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-surface-card dark:hover:bg-white/10"
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
                    class="list-none flex items-center gap-sm cursor-pointer select-none px-sm py-xs rounded-full hover:bg-surface-card dark:hover:bg-white/10">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}"
                            class="w-8 h-8 rounded-full object-cover">
                    @else
                        <span
                            class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-caption-md font-semibold">
                            {{ auth()->user()->initials() }}
                        </span>
                    @endif
                    <span class="hidden sm:inline text-body-strong text-ink dark:text-on-dark">{{ auth()->user()->name }}</span>
                </summary>

                <div
                    class="absolute right-0 mt-sm w-56 bg-canvas dark:bg-surface-dark border border-hairline dark:border-white/10 rounded-md shadow-modal py-xs z-50">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-md py-sm text-body-sm text-ink dark:text-on-dark hover:bg-surface-card dark:hover:bg-white/10">
                        Profil Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-md py-sm text-body-sm text-error hover:bg-surface-card dark:hover:bg-white/10">
                            Keluar
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    {{-- Mobile-only dim backdrop behind the off-canvas sidebar --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-black/50 md:hidden"
    ></div>

    <div class="flex">
        {{-- Sidebar: static column on md+, off-canvas drawer below md --}}
        <aside
            class="fixed md:static top-16 md:top-auto bottom-0 md:bottom-auto left-0 z-30
                   w-72 md:w-64 shrink-0 min-h-[calc(100vh-4rem)]
                   bg-surface-card dark:bg-white/[0.04] border-r border-hairline dark:border-white/10
                   px-md py-lg flex flex-col gap-xxs overflow-y-auto
                   transition-transform duration-200 ease-out
                   -translate-x-full md:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
        >
            <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
                class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('dashboard') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/10' }}">
                Dashboard
            </a>

            {{-- Modul 4: Ticket Management --}}
            <a href="{{ route('tickets.index') }}" @click="sidebarOpen = false"
                class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('tickets.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/10' }}">
                Tickets
            </a>

            @can('viewAny', \App\Models\KnowledgeBaseArticle::class)
                <a href="{{ route('knowledge-base.index') }}" @click="sidebarOpen = false"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('knowledge-base.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/10' }}">
                    Knowledge Base
                </a>
            @endcan

            @can('viewAny', \App\Models\Asset::class)
                <a href="{{ route('assets.index') }}" @click="sidebarOpen = false"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('assets.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/10' }}">
                    Assets
                </a>
            @endcan

            {{-- Modul 8: Report --}}
            @if(auth()->user()->hasPermission('report.view'))
                <a href="{{ route('reports.index') }}" @click="sidebarOpen = false"
                    class="px-md py-sm rounded-md text-body-strong {{ request()->routeIs('reports.*') ? 'bg-ink text-on-dark' : 'text-ink dark:text-on-dark hover:bg-canvas dark:hover:bg-white/10' }}">
                    Reports
                </a>
            @endif

            <span class="mt-md mb-xxs px-md text-caption-md text-ash uppercase tracking-wide">Segera Hadir</span>
            @foreach (['Bonus Features'] as $upcoming)
                <span class="px-md py-sm rounded-md text-body-strong text-stone cursor-not-allowed">{{ $upcoming }}</span>
            @endforeach
        </aside>

        <main class="flex-1 min-w-0 px-lg md:px-xl py-xl">
            @include('partials.flash-messages')
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        window.helpdeskToggleTheme = function () {
            var root = document.documentElement;
            var isDark = root.classList.toggle('dark');
            localStorage.setItem('helpdesk-theme', isDark ? 'dark' : 'light');
        };
    </script>
</body>

</html>