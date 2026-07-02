@extends('layouts.app')

@section('content')
    <div class="max-w-2xl flex flex-col gap-xl">
        <div>
            <h1 class="text-heading-xl text-ink dark:text-on-dark mb-xxs">Profil Saya</h1>
            <p class="text-body-md text-mute">Kelola informasi akun, foto profil, dan preferensi tampilan.</p>
        </div>

        {{-- Profile information + avatar --}}
        <section class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Informasi Akun</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-lg">
                @csrf
                @method('PATCH')

                <div class="flex items-center gap-lg">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-full object-cover">
                    @else
                        <span class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-heading-md font-semibold">
                            {{ $user->initials() }}
                        </span>
                    @endif

                    <div class="flex-1">
                        <label for="avatar" class="field-label">Foto Profil</label>
                        <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp"
                               class="block w-full text-body-sm text-mute file:mr-md file:py-xs file:px-md file:rounded-full file:border-0 file:bg-secondary-bg file:text-body-sm file:text-ink hover:file:bg-secondary-pressed">
                        @error('avatar')
                            <p class="field-error">{{ $message }}</p>
                        @enderror

                        @if($user->avatar)
                            <label class="flex items-center gap-xs mt-xs text-body-sm text-mute">
                                <input type="checkbox" name="remove_avatar" value="1" class="rounded-sm border-ash">
                                Hapus foto profil
                            </label>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-lg">
                    <div>
                        <label for="name" class="field-label">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="field-input @error('name') has-error @enderror">
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="field-label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="field-input @error('email') has-error @enderror">
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="field-label">Nomor HP</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="field-input @error('phone') has-error @enderror">
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="position" class="field-label">Jabatan</label>
                        <input id="position" type="text" name="position" value="{{ old('position', $user->position) }}"
                               class="field-input @error('position') has-error @enderror">
                        @error('position') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="locale" class="field-label">Bahasa</label>
                        <select id="locale" name="locale" class="field-input">
                            <option value="id" @selected(old('locale', $user->locale) === 'id')>Bahasa Indonesia</option>
                            <option value="en" @selected(old('locale', $user->locale) === 'en')>English</option>
                        </select>
                    </div>

                    <div>
                        <label for="theme" class="field-label">Tema</label>
                        <select id="theme" name="theme" class="field-input">
                            <option value="light" @selected(old('theme', $user->theme) === 'light')>Terang (Light)</option>
                            <option value="dark" @selected(old('theme', $user->theme) === 'dark')>Gelap (Dark)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-lg text-body-sm text-mute">
                    <p><span class="text-ink dark:text-on-dark font-semibold">Divisi:</span> {{ $user->division ?? '—' }}</p>
                    <p><span class="text-ink dark:text-on-dark font-semibold">Role:</span> {{ $user->role?->name ?? '—' }}</p>
                </div>

                <div>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </section>

        {{-- Change password --}}
        <section class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
            <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ubah Kata Sandi</h2>

            <form method="POST" action="{{ route('profile.password.update') }}" class="flex flex-col gap-lg max-w-md">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="field-label">Kata Sandi Saat Ini</label>
                    <input id="current_password" type="password" name="current_password" required
                           class="field-input @error('current_password', 'updatePassword') has-error @enderror">
                    @error('current_password', 'updatePassword')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="field-label">Kata Sandi Baru</label>
                    <input id="new_password" type="password" name="password" required
                           class="field-input @error('password', 'updatePassword') has-error @enderror">
                    @error('password', 'updatePassword')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="field-label">Konfirmasi Kata Sandi Baru</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="field-input">
                </div>

                <div>
                    <button type="submit" class="btn-secondary">Ubah Kata Sandi</button>
                </div>
            </form>
        </section>
    </div>
@endsection
