@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Ticket Terhapus</h1>
            <p class="text-body-md text-mute">Ticket yang dihapus (soft delete) — bisa dipulihkan kapan saja.</p>
        </div>
        <a href="{{ route('tickets.index') }}" class="btn-tertiary">&larr; Kembali ke Daftar Ticket</a>
    </div>

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 overflow-x-auto">
        <table class="w-full text-body-sm">
            <thead>
                <tr class="border-b border-hairline dark:border-white/10 text-left text-mute">
                    <th class="px-lg py-md font-semibold">No. Ticket</th>
                    <th class="px-lg py-md font-semibold">Judul</th>
                    <th class="px-lg py-md font-semibold">Dibuat oleh</th>
                    <th class="px-lg py-md font-semibold">Dihapus</th>
                    <th class="px-lg py-md font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr class="border-b border-hairline dark:border-white/10 last:border-0">
                        <td class="px-lg py-md text-body-strong text-ink dark:text-on-dark">{{ $ticket->ticket_number }}</td>
                        <td class="px-lg py-md text-mute truncate max-w-[280px]">{{ $ticket->subject }}</td>
                        <td class="px-lg py-md text-mute">{{ $ticket->creator->name ?? '—' }}</td>
                        <td class="px-lg py-md text-mute">{{ $ticket->deleted_at?->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-lg py-md">
                            <form method="POST" action="{{ route('tickets.restore', $ticket->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">Pulihkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-lg py-xxl text-center text-mute">Tidak ada ticket terhapus.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $tickets->links() }}</div>
</div>
@endsection
