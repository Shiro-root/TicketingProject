@extends('layouts.guest')

@section('content')
    <div class="auth-card">
        <h1 class="text-heading-lg text-ink mb-xxs">Lupa kata sandi?</h1>
        <p class="text-body-md text-mute mb-xl">
            Masukkan email Anda, kami akan mengirimkan tautan untuk membuat kata sandi baru.
        </p>

        @session('status')
            <div class="mb-lg rounded-md bg-success-pale text-success-deep text-body-sm px-md py-sm">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-lg">
            @csrf

            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="field-input @error('email') has-error @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">Kirim Tautan Reset</button>
        </form>

        <p class="text-caption-sm text-mute mt-xl text-center">
            <a href="{{ route('login') }}" class="link-inline">&larr; Kembali ke halaman masuk</a>
        </p>
    </div>
@endsection
