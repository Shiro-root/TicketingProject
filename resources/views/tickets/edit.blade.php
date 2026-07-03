@extends('layouts.app')

@section('content')
<div class="flex flex-col gap-xl max-w-5xl">
    <div>
        <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Edit Ticket {{ $ticket->ticket_number }}</h1>
        <p class="text-body-md text-mute">Perbarui informasi ticket.</p>
    </div>

    <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data" class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl flex flex-col gap-xl">
        @csrf
        @method('PUT')

        @include('tickets._form')

        <div class="flex justify-end gap-md pt-md border-t border-hairline dark:border-white/10">
            <a href="{{ route('tickets.show', $ticket) }}" class="btn-tertiary">Batal</a>
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
