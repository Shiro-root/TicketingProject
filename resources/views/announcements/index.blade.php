@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Pengumuman</h1>
            <p class="text-body-md text-mute">Kelola banner pengumuman yang tampil untuk semua pengguna.</p>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn-primary">+ Buat Pengumuman</a>
    </div>

    @include('partials.flash-messages')

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">Judul</th>
                    <th class="px-lg py-md font-semibold">Jenis</th>
                    <th class="px-lg py-md font-semibold">Periode</th>
                    <th class="px-lg py-md font-semibold">Status</th>
                    <th class="px-lg py-md font-semibold">Dibuat oleh</th>
                    <th class="px-lg py-md font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($announcements as $announcement)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                        <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark truncate max-w-[280px]">{{ $announcement->title }}</td>
                        <td class="px-lg py-md text-mute capitalize">{{ $announcement->type }}</td>
                        <td class="px-lg py-md text-mute">
                            {{ $announcement->starts_at?->translatedFormat('d M Y H:i') ?? 'Selalu' }}
                            &rarr;
                            {{ $announcement->ends_at?->translatedFormat('d M Y H:i') ?? 'Tanpa batas' }}
                        </td>
                        <td class="px-lg py-md">
                            @if($announcement->is_active)
                                <span class="text-caption-md px-sm py-xxs rounded-full bg-success-pale text-success-deep">Aktif</span>
                            @else
                                <span class="text-caption-md px-sm py-xxs rounded-full bg-stone/40 text-charcoal">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-lg py-md text-mute">{{ $announcement->creator->name ?? '—' }}</td>
                        <td class="px-lg py-md">
                            <div class="flex items-center gap-sm">
                                <a href="{{ route('announcements.edit', $announcement) }}" class="btn-tertiary">Edit</a>
                                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}"
                                      onsubmit="return confirm('Hapus pengumuman ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-tertiary text-error">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-lg py-xxl text-center text-mute">Belum ada pengumuman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $announcements->links() }}</div>
</div>
@endsection
