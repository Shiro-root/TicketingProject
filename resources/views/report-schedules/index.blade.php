@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Laporan Terjadwal</h1>
            <p class="text-body-md text-mute">Kirim laporan ticket otomatis via email secara berkala.</p>
        </div>
        <a href="{{ route('report-schedules.create') }}" class="btn-primary">+ Buat Jadwal</a>
    </div>

    @include('partials.flash-messages')

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">Nama</th>
                    <th class="px-lg py-md font-semibold">Frekuensi</th>
                    <th class="px-lg py-md font-semibold">Format</th>
                    <th class="px-lg py-md font-semibold">Penerima</th>
                    <th class="px-lg py-md font-semibold">Terakhir Terkirim</th>
                    <th class="px-lg py-md font-semibold">Status</th>
                    <th class="px-lg py-md font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                        <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark">{{ $schedule->name }}</td>
                        <td class="px-lg py-md text-mute capitalize">{{ $schedule->frequency }}</td>
                        <td class="px-lg py-md text-mute uppercase">{{ $schedule->format }}</td>
                        <td class="px-lg py-md text-mute truncate max-w-[220px]">{{ implode(', ', $schedule->recipients) }}</td>
                        <td class="px-lg py-md text-mute">{{ $schedule->last_sent_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah' }}</td>
                        <td class="px-lg py-md">
                            @if($schedule->is_active)
                                <span class="text-caption-md px-sm py-xxs rounded-full bg-success-pale text-success-deep">Aktif</span>
                            @else
                                <span class="text-caption-md px-sm py-xxs rounded-full bg-stone/40 text-charcoal">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-lg py-md">
                            <div class="flex items-center gap-sm">
                                <a href="{{ route('report-schedules.edit', $schedule) }}" class="btn-tertiary">Edit</a>
                                <form method="POST" action="{{ route('report-schedules.destroy', $schedule) }}"
                                      onsubmit="return confirm('Hapus jadwal laporan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-tertiary text-error">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-lg py-xxl text-center text-mute">Belum ada jadwal laporan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $schedules->links() }}</div>
</div>
@endsection
