@if (session('status'))
    <div class="mb-lg rounded-md bg-success-pale text-success-deep text-body-sm px-md py-sm">
        @switch(session('status'))
            @case('profile-updated')
                Profil berhasil diperbarui.
                @break
            @case('password-updated')
                Kata sandi berhasil diubah.
                @break
            @case('notification-settings-updated')
                Preferensi notifikasi berhasil disimpan.
                @break
            @case('article-created')
                Artikel berhasil dipublikasikan.
                @break
            @case('article-updated')
                Artikel berhasil diperbarui.
                @break
            @case('article-deleted')
                Artikel berhasil dihapus.
                @break
            @case('article-restored')
                Artikel berhasil dipulihkan.
                @break
            @case('asset-created')
                Asset berhasil ditambahkan.
                @break
            @case('asset-updated')
                Asset berhasil diperbarui.
                @break
            @case('asset-deleted')
                Asset berhasil dihapus.
                @break
            @case('asset-restored')
                Asset berhasil dipulihkan.
                @break
            @case('saved-filter-created')
                Filter berhasil disimpan.
                @break
            @case('saved-filter-deleted')
                Filter tersimpan berhasil dihapus.
                @break
            @case('announcement-created')
                Pengumuman berhasil dipublikasikan.
                @break
            @case('announcement-updated')
                Pengumuman berhasil diperbarui.
                @break
            @case('announcement-deleted')
                Pengumuman berhasil dihapus.
                @break
            @case('bulk-action-done')
                Bulk action selesai dijalankan — lihat ringkasan di bawah.
                @break
            @case('report-schedule-created')
                Jadwal laporan berhasil dibuat.
                @break
            @case('report-schedule-updated')
                Jadwal laporan berhasil diperbarui.
                @break
            @case('report-schedule-deleted')
                Jadwal laporan berhasil dihapus.
                @break
            @case('maintenance-enabled')
                Maintenance Mode diaktifkan.
                @break
            @case('maintenance-disabled')
                Maintenance Mode dinonaktifkan — aplikasi kembali normal.
                @break
            @default
                {{ session('status') }}
        @endswitch
    </div>
@endif

@if (session('bulk_summary'))
    <div class="mb-lg rounded-md bg-surface-card dark:bg-white/5 text-ink dark:text-on-dark text-body-sm px-md py-sm">
        {{ session('bulk_summary') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-lg rounded-md bg-error/10 text-error text-body-sm px-md py-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
