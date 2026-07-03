@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-4xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Edit Artikel</h1>
        <p class="text-body-md text-mute">{{ $article->title }}</p>
    </div>

    <form method="POST" action="{{ route('knowledge-base.update', $article) }}" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-lg">
        @csrf
        @method('PUT')
        @include('knowledge-base._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('knowledge-base.show', $article) }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection