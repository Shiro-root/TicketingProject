@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-2xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Maintenance Mode</h1>
        <p class="text-body-md text-mute">Nonaktifkan sementara akses aplikasi untuk pengguna biasa saat melakukan pemeliharaan.</p>
    </div>

    @include('partials.flash-messages')

    @if($secret)
        <div class="rounded-md bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/40 p-lg">
            <p class="text-body-strong text-amber-900 dark:text-amber-200 mb-xs">⚠️ Simpan tautan bypass ini sekarang — hanya ditampilkan sekali!</p>
            <p class="text-body-sm text-amber-900 dark:text-amber-200 break-all">
                {{ url('/'.$secret) }}
            </p>
            <p class="text-caption-sm text-amber-800 dark:text-amber-300 mt-xs">
                Buka tautan ini di browser Anda untuk tetap bisa mengakses aplikasi selagi mode maintenance aktif untuk pengguna lain.
            </p>
        </div>
    @endif

    <div class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
        <div class="flex items-center gap-sm mb-lg">
            <span class="w-3 h-3 rounded-full {{ $isDown ? 'bg-error' : 'bg-success-deep' }}"></span>
            <p class="text-body-strong text-ink dark:text-on-dark">
                Status saat ini: {{ $isDown ? 'AKTIF (aplikasi sedang down untuk pengguna biasa)' : 'Normal (aplikasi berjalan seperti biasa)' }}
            </p>
        </div>

        @if(! $isDown)
            <form method="POST" action="{{ route('settings.maintenance.enable') }}" class="flex flex-col gap-lg">
                @csrf
                <div>
                    <label for="message" class="field-label">Pesan untuk pengguna (opsional)</label>
                    <textarea id="message" name="message" rows="2"
                              placeholder="Sedang dalam pemeliharaan terjadwal. Silakan coba beberapa saat lagi."
                              class="field-input"></textarea>
                </div>
                <div>
                    <label for="retry" class="field-label">Retry-After (detik, opsional)</label>
                    <input id="retry" type="number" min="0" name="retry" value="60" class="field-input max-w-[160px]">
                    <p class="text-caption-sm text-mute mt-xs">Memberi tahu browser pengguna kapan boleh mencoba lagi.</p>
                </div>
                <button type="submit" class="btn-primary self-start" onclick="return confirm('Aktifkan Maintenance Mode? Semua pengguna biasa tidak akan bisa mengakses aplikasi.');">
                    Aktifkan Maintenance Mode
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('settings.maintenance.disable') }}">
                @csrf
                <button type="submit" class="btn-primary">Nonaktifkan Maintenance Mode</button>
            </form>
        @endif
    </div>
</div>
@endsection
