@extends('layouts.guest')

@section('content')
    <div class="auth-card">
        <h1 class="text-heading-lg text-ink mb-xxs">Selamat datang kembali</h1>
        <p class="text-body-md text-mute mb-xl">Masuk ke akun Helpdesk Enterprise Anda.</p>

        @session('status')
            <div class="mb-lg rounded-md bg-success-pale text-success-deep text-body-sm px-md py-sm">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-lg">
            @csrf

            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username"
                       class="field-input @error('email') has-error @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-xs">
                    <label for="password" class="field-label mb-0">Kata Sandi</label>
                    <a href="{{ route('password.request') }}" class="link-inline text-body-sm">Lupa kata sandi?</a>
                </div>
                <input id="password" type="password" name="password" required
                       autocomplete="current-password"
                       class="field-input @error('password') has-error @enderror">
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-xs text-body-sm text-body select-none">
                <input type="checkbox" name="remember" class="rounded-sm border-ash text-primary focus:ring-focus-outer">
                Ingat saya
            </label>

            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>

        <p class="text-caption-sm text-mute mt-xl text-center">
            Tidak punya akun? Hubungi Administrator IT Anda.
        </p>
    </div>
@endsection
