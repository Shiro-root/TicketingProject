@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-3xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Buat Jadwal Laporan</h1>
        <p class="text-body-md text-mute">Laporan akan dikirim otomatis ke email penerima sesuai frekuensi yang dipilih.</p>
    </div>

    <form method="POST" action="{{ route('report-schedules.store') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @include('report-schedules._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('report-schedules.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Jadwal</button>
        </div>
    </form>
</div>
@endsection
