@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-3xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Edit Jadwal Laporan</h1>
        <p class="text-body-md text-mute">{{ $schedule->name }}</p>
    </div>

    <form method="POST" action="{{ route('report-schedules.update', $schedule) }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @method('PUT')
        @include('report-schedules._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('report-schedules.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
