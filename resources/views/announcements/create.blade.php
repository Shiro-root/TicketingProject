@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-3xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Buat Pengumuman</h1>
        <p class="text-body-md text-mute">Pengumuman akan tampil sebagai banner di atas halaman untuk semua pengguna.</p>
    </div>

    <form method="POST" action="{{ route('announcements.store') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @include('announcements._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('announcements.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Publikasikan</button>
        </div>
    </form>
</div>
@endsection
