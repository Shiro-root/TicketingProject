@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-4xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Tulis Artikel Baru</h1>
        <p class="text-body-md text-mute">Bagikan solusi atau dokumentasi untuk tim dan pengguna.</p>
    </div>

    <form method="POST" action="{{ route('knowledge-base.store') }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @include('knowledge-base._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('knowledge-base.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Publikasikan</button>
        </div>
    </form>
</div>
@endsection