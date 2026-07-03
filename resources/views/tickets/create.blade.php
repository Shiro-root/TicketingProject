@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-5xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Buat Ticket Baru</h1>
        <p class="text-body-md text-mute">Jelaskan kendala Anda secara detail agar teknisi bisa membantu lebih cepat.</p>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-xl">
        @csrf

        @include('tickets._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('tickets.index') }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Buat Ticket</button>
        </div>
    </form>
</div>
@endsection
