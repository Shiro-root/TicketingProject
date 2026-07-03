@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl">
    <div class="flex items-start justify-between flex-wrap gap-md">
        <div>
            <div class="flex items-center gap-sm flex-wrap mb-xxs">
                <h1 class="text-heading-xl text-ink dark:text-on-dark">{{ $asset->asset_tag }}</h1>
                <x-asset-status-badge :label="$asset->status->label()" :status="$asset->status->value" />
                @if($asset->warranty_expiry && ! $asset->isUnderWarranty())
                    <span class="text-caption-md px-sm py-xxs rounded-full bg-error text-white">Garansi Habis</span>
                @endif
            </div>
            <p class="text-heading-md text-ink dark:text-on-dark">{{ $asset->name }}</p>
        </div>

        <div class="flex items-center gap-sm">
            @can('update', $asset)
                <a href="{{ route('assets.edit', $asset) }}" class="btn-secondary">Edit</a>
            @endcan
            @can('delete', $asset)
                <form method="POST" action="{{ route('assets.destroy', $asset) }}"
                      onsubmit="return confirm('Hapus asset ini? Bisa dipulihkan lewat menu Restore.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-tertiary text-error">Hapus</button>
                </form>
            @endcan
        </div>
    </div>

    @include('partials.flash-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-xl">
        <div class="lg:col-span-2 flex flex-col gap-xl">
            <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
                <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Informasi Asset</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-lg text-body-sm">
                    <div><dt class="text-mute mb-xxs">Jenis</dt><dd class="text-ink dark:text-on-dark">{{ $asset->type->label() }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Brand</dt><dd class="text-ink dark:text-on-dark">{{ $asset->brand ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Model</dt><dd class="text-ink dark:text-on-dark">{{ $asset->model ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Serial Number</dt><dd class="text-ink dark:text-on-dark">{{ $asset->serial_number ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Lokasi</dt><dd class="text-ink dark:text-on-dark">{{ $asset->location ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Department</dt><dd class="text-ink dark:text-on-dark">{{ $asset->department->name ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Ditugaskan ke</dt><dd class="text-ink dark:text-on-dark">{{ $asset->assignedUser->name ?? '— Tidak ditugaskan' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Tanggal Pembelian</dt><dd class="text-ink dark:text-on-dark">{{ $asset->purchase_date?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Harga Pembelian</dt><dd class="text-ink dark:text-on-dark">{{ $asset->purchase_price ? 'Rp '.number_format($asset->purchase_price, 0, ',', '.') : '—' }}</dd></div>
                    <div><dt class="text-mute mb-xxs">Berakhir Garansi</dt><dd class="{{ $asset->warranty_expiry && ! $asset->isUnderWarranty() ? 'text-error font-semibold' : 'text-ink dark:text-on-dark' }}">{{ $asset->warranty_expiry?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                </dl>

                @if($asset->notes)
                    <div class="mt-lg pt-lg border-t border-hairline dark:border-white/10">
                        <p class="text-mute text-body-sm mb-xxs">Catatan</p>
                        <p class="text-body-md text-ink dark:text-on-dark whitespace-pre-line">{{ $asset->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Riwayat ticket terkait asset --}}
            <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
                <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ticket Terkait</h2>
                <div class="flex flex-col gap-md">
                    @forelse ($asset->tickets as $ticket)
                        <div class="flex items-center justify-between border-b border-hairline dark:border-white/10 pb-md last:border-0 last:pb-0">
                            <div class="min-w-0">
                                <a href="{{ route('tickets.show', $ticket) }}" class="text-body-strong text-ink dark:text-on-dark hover:underline">
                                    {{ $ticket->ticket_number }}
                                </a>
                                <p class="text-body-sm text-mute truncate">{{ $ticket->subject }}</p>
                            </div>
                            <x-status-badge :label="$ticket->status->label()" :color="$ticket->status->color()" />
                        </div>
                    @empty
                        <p class="text-body-sm text-mute">Belum ada ticket yang terhubung ke asset ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-lg">
            <div class="bg-surface-card dark:bg-white/5 rounded-md p-lg">
                <h2 class="text-body-strong text-ink dark:text-on-dark mb-md">Ringkasan</h2>
                <div class="flex flex-col gap-sm text-body-sm">
                    <div class="flex justify-between"><span class="text-mute">Total Ticket</span><span class="text-ink dark:text-on-dark font-semibold">{{ $asset->tickets->count() }}</span></div>
                    <div class="flex justify-between"><span class="text-mute">Status Garansi</span><span class="{{ $asset->isUnderWarranty() ? 'text-success-deep' : 'text-error' }} font-semibold">{{ $asset->warranty_expiry ? ($asset->isUnderWarranty() ? 'Aktif' : 'Habis') : 'Tidak Ada Data' }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection