@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Halo, {{ auth()->user()->name }} 👋</h1>
        <p class="text-body-md text-mute">Ringkasan ticket yang Anda buat.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-lg">
        @foreach ([
            'Total Ticket' => $statusCounts['total'],
            'Sedang Berjalan' => $statusCounts['open'],
            'Resolved' => $statusCounts['resolved'],
            'Closed' => $statusCounts['closed'],
        ] as $label => $value)
            <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-lg">
                <p class="text-body-sm text-mute mb-xxs">{{ $label }}</p>
                <p class="text-heading-xl text-ink dark:text-on-dark">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
        <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket Terbaru Saya</h2>
        <div class="flex flex-col gap-md">
            @forelse ($latestTickets as $ticket)
                <div class="flex items-center justify-between border-b border-hairline dark:border-white/10 pb-md last:border-0 last:pb-0">
                    <div>
                        <p class="text-body-strong text-ink dark:text-on-dark">{{ $ticket->ticket_number }}</p>
                        <p class="text-body-sm text-mute">{{ $ticket->subject }}</p>
                    </div>
                    <x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                </div>
            @empty
                <p class="text-body-sm text-mute">Anda belum membuat ticket.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
