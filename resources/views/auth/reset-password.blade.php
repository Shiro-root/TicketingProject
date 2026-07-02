@extends('layouts.guest')

@section('content')
    <div class="auth-card">
        <h1 class="text-heading-lg text-ink mb-xxs">Buat kata sandi baru</h1>
        <p class="text-body-md text-mute mb-xl">Gunakan kata sandi yang kuat dan belum pernah dipakai sebelumnya.</p>

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-lg">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus
                       class="field-input @error('email') has-error @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="field-label">Kata Sandi Baru</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="field-input @error('password') has-error @enderror">
                @error('password')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="field-label">Konfirmasi Kata Sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       autocomplete="new-password" class="field-input">
            </div>

            <button type="submit" class="btn-primary w-full">Simpan Kata Sandi</button>
        </form>
    </div>
@endsection
