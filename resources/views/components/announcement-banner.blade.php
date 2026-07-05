@php($activeAnnouncements = \App\Models\Announcement::currentlyActive()->latest()->get())

@if($activeAnnouncements->isNotEmpty())
<div id="announcement-banner-stack" class="flex flex-col gap-xs mb-lg">
    @foreach ($activeAnnouncements as $announcement)
        @php($classes = match ($announcement->type) {
            'warning' => 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/10 dark:text-amber-200 dark:border-amber-900/40',
            'success' => 'bg-success-pale text-success-deep border-success-deep/20 dark:bg-success-deep/10 dark:border-success-deep/30',
            'danger' => 'bg-error/10 text-error border-error/20 dark:bg-error/10 dark:border-error/30',
            default => 'bg-surface-card text-ink border-hairline dark:bg-white/5 dark:text-on-dark dark:border-white/10',
        })
        <div class="announcement-item flex items-start justify-between gap-md px-lg py-md rounded-md border {{ $classes }}" data-id="{{ $announcement->id }}">
            <div>
                <p class="text-body-strong">{{ $announcement->title }}</p>
                <p class="text-body-sm">{{ $announcement->content }}</p>
            </div>
            <button type="button" class="dismiss-announcement text-caption-sm shrink-0 opacity-70 hover:opacity-100" data-id="{{ $announcement->id }}">
                ✕ Tutup
            </button>
        </div>
    @endforeach
</div>

<script>
    (function () {
        const STORAGE_KEY = 'helpdesk_dismissed_announcements';
        const dismissed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');

        document.querySelectorAll('.announcement-item').forEach((el) => {
            if (dismissed.includes(Number(el.dataset.id))) {
                el.remove();
            }
        });

        document.querySelectorAll('.dismiss-announcement').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.dataset.id);
                const list = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                if (! list.includes(id)) {
                    list.push(id);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
                }
                btn.closest('.announcement-item')?.remove();
            });
        });
    })();
</script>
@endif
