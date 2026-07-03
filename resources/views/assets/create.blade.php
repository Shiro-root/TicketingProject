@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-4xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Tambah Asset Baru</h1>
        <p class="text-body-md text-mute">Daftarkan perangkat IT baru ke dalam inventaris.</p>
    </div>

    <form method="POST" action="{{ route('assets.store') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @include('assets._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('assets.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Asset</button>
        </div>
    </form>
</div>
@endsection