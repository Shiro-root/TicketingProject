{{--
    Bonus Feature: Keyboard Shortcut.
    Diikutsertakan sekali di layouts/app.blade.php. Shortcut dinonaktifkan otomatis
    saat fokus sedang di input/textarea/select/contenteditable supaya tidak
    mengganggu pengetikan biasa.
--}}
<div id="shortcut-help-modal" class="hidden fixed inset-0 z-40 flex items-center justify-center bg-black/50 px-lg">
    <div class="bg-canvas dark:bg-surface-dark rounded-lg shadow-modal p-xxl max-w-md w-full">
        <h2 class="text-heading-lg text-ink dark:text-on-dark mb-lg">Keyboard Shortcut</h2>
        <dl class="flex flex-col gap-sm text-body-sm">
            <div class="flex justify-between"><dt class="text-mute">Buat ticket baru</dt><dd><kbd class="shortcut-key">c</kbd></dd></div>
            <div class="flex justify-between"><dt class="text-mute">Fokus ke pencarian</dt><dd><kbd class="shortcut-key">/</kbd></dd></div>
            <div class="flex justify-between"><dt class="text-mute">Ke Dashboard</dt><dd><kbd class="shortcut-key">g</kbd> lalu <kbd class="shortcut-key">d</kbd></dd></div>
            <div class="flex justify-between"><dt class="text-mute">Ke Daftar Ticket</dt><dd><kbd class="shortcut-key">g</kbd> lalu <kbd class="shortcut-key">t</kbd></dd></div>
            @if(auth()->user()?->hasPermission('report.view'))
                <div class="flex justify-between"><dt class="text-mute">Ke Reports</dt><dd><kbd class="shortcut-key">g</kbd> lalu <kbd class="shortcut-key">r</kbd></dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-mute">Tampilkan bantuan ini</dt><dd><kbd class="shortcut-key">?</kbd></dd></div>
            <div class="flex justify-between"><dt class="text-mute">Tutup dialog ini</dt><dd><kbd class="shortcut-key">Esc</kbd></dd></div>
        </dl>
        <button type="button" id="shortcut-help-close" class="btn-secondary w-full mt-xl">Tutup</button>
    </div>
</div>

<style>
    .shortcut-key {
        display: inline-block; padding: 2px 8px; border-radius: 6px;
        background: var(--color-surface-card); border: 1px solid var(--color-hairline);
        font-family: monospace; font-size: 12px;
    }
    html.dark .shortcut-key { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); }
</style>

<script>
    (function () {
        const routes = {
            createTicket: @json(\Illuminate\Support\Facades\Route::has('tickets.create') && auth()->user()?->can('create', \App\Models\Ticket::class) ? route('tickets.create') : null),
            dashboard: @json(route('dashboard')),
            tickets: @json(route('tickets.index')),
            reports: @json(auth()->user()?->hasPermission('report.view') ? route('reports.index') : null),
        };

        const helpModal = document.getElementById('shortcut-help-modal');
        let pendingG = false;
        let pendingGTimeout = null;

        function isTypingContext(target) {
            const tag = (target.tagName || '').toLowerCase();
            return tag === 'input' || tag === 'textarea' || tag === 'select' || target.isContentEditable;
        }

        function showHelp() { helpModal.classList.remove('hidden'); }
        function hideHelp() { helpModal.classList.add('hidden'); }

        document.getElementById('shortcut-help-close')?.addEventListener('click', hideHelp);
        helpModal?.addEventListener('click', (e) => { if (e.target === helpModal) hideHelp(); });

        document.addEventListener('keydown', function (e) {
            if (e.metaKey || e.ctrlKey || e.altKey) return;

            // Esc selalu boleh, termasuk saat sedang mengetik.
            if (e.key === 'Escape') { hideHelp(); return; }

            if (isTypingContext(e.target)) return;

            if (pendingG) {
                pendingG = false;
                clearTimeout(pendingGTimeout);
                if (e.key === 'd' && routes.dashboard) { window.location.href = routes.dashboard; }
                if (e.key === 't' && routes.tickets) { window.location.href = routes.tickets; }
                if (e.key === 'r' && routes.reports) { window.location.href = routes.reports; }
                return;
            }

            switch (e.key) {
                case 'c':
                    if (routes.createTicket) window.location.href = routes.createTicket;
                    break;
                case '/':
                    e.preventDefault();
                    document.querySelector('[data-shortcut="search-input"]')?.focus();
                    break;
                case 'g':
                    pendingG = true;
                    pendingGTimeout = setTimeout(() => { pendingG = false; }, 1200);
                    break;
                case '?':
                    showHelp();
                    break;
            }
        });
    })();
</script>
