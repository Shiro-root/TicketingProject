@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-3xl">
    <div class="flex items-center justify-between flex-wrap gap-md">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Notifikasi</h1>
            <p class="text-body-md text-mute">Riwayat notifikasi ticket Anda.</p>
        </div>
        @if($notifications->total())
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn-tertiary">Tandai Semua Dibaca</button>
            </form>
        @endif
    </div>

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 divide-y divide-hairline dark:divide-white/10">
        @forelse ($notifications as $notification)
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}"
                  class="{{ $notification->read_at ? '' : 'bg-surface-card dark:bg-white/5' }}">
                @csrf
                <button type="submit" class="w-full text-left px-lg py-md flex items-start gap-md hover:bg-surface-card dark:hover:bg-white/5">
                    <span class="text-heading-md">{{ $notification->data['icon'] ?? '🔔' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-body-sm text-ink dark:text-on-dark">{{ $notification->data['message'] }}</p>
                        <p class="text-caption-sm text-mute mt-xxs">{{ $notification->data['ticket_number'] ?? '' }} · {{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless($notification->read_at)
                        <span class="w-2 h-2 rounded-full bg-primary mt-xs shrink-0"></span>
                    @endunless
                </button>
            </form>
        @empty
            <p class="px-lg py-xxl text-center text-body-sm text-mute">Belum ada notifikasi.</p>
        @endforelse
    </div>

    <div>{{ $notifications->links() }}</div>
</div>
@endsection