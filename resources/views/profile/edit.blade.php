@extends('layouts.app')

@section('content')
    <div class="max-w-3xl flex flex-col gap-xl">

        {{-- ────────────────────────────────────────────────────────────
             Hero header — {rounded.lg} (32px). Only the AVATAR is allowed
             to overlap the red band; every piece of text (name, position,
             badges, last-login) sits fully on the canvas surface below it.
             This is the fix for the previous bug where dark/black text
             ("text-ink") landed on top of the red gradient and became
             unreadable — text now never shares space with the red band.
        ──────────────────────────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-lg border border-hairline dark:border-white/10 bg-canvas dark:bg-white/[0.04]">
            {{-- quiet gradient wash, single brand accent only — purely decorative, no text on top of it --}}
            <div class="h-20 bg-gradient-to-r from-primary/90 to-primary/60"></div>

            <div class="px-xl pb-xl">
                {{-- Avatar overlaps the red band by design; it's an image/initials
                     chip (not small text), so contrast against red is intentional
                     and fine — this is the only element allowed to sit on the band. --}}
                <div class="relative w-fit -mt-10">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                             class="w-20 h-20 rounded-full object-cover ring-4 ring-canvas dark:ring-surface-dark">
                    @else
                        <span class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-heading-lg font-semibold ring-4 ring-canvas dark:ring-surface-dark">
                            {{ $user->initials() }}
                        </span>
                    @endif

                    <label for="avatar"
                           class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-ink dark:bg-white text-white dark:text-ink
                                  flex items-center justify-center text-caption-md cursor-pointer shadow-modal
                                  hover:opacity-90 transition-opacity"
                           title="Ganti foto profil">
                        📷
                    </label>
                </div>

                {{-- Everything below here sits on the plain canvas surface —
                     safe, theme-aware contrast in both light and dark mode. --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-md mt-md">
                    <div class="min-w-0">
                        <h1 class="text-heading-xl text-ink dark:text-on-dark truncate">{{ $user->name }}</h1>
                        <p class="text-body-sm text-mute truncate">{{ $user->position ?? 'Belum ada jabatan' }}</p>

                        <div class="flex flex-wrap items-center gap-xs mt-sm">
                            <span class="text-caption-md px-md py-xxs rounded-full bg-ink text-on-dark dark:bg-white dark:text-ink font-medium">
                                {{ $user->role?->name ?? '—' }}
                            </span>
                            @if($user->division)
                                <span class="text-caption-md px-md py-xxs rounded-full bg-surface-card dark:bg-white/10 text-mute">
                                    {{ $user->division }}
                                </span>
                            @endif
                            <span class="text-caption-md px-md py-xxs rounded-full bg-surface-card dark:bg-white/10 text-mute">
                                Bergabung {{ $user->created_at->translatedFormat('M Y') }}
                            </span>
                        </div>
                    </div>

                    @if($user->last_login_at)
                        <div class="sm:text-right shrink-0">
                            <p class="text-caption-sm text-mute">Login terakhir</p>
                            <p class="text-body-sm text-ink dark:text-on-dark font-medium">{{ $user->last_login_at->diffForHumans() }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash messages already render once in layouts/app.blade.php's <main>
             wrapper — including it again here was producing the duplicate
             "Profil berhasil diperbarui." banner. Intentionally not repeated. --}}

        {{-- ────────────────────────────────────────────────────────────
             Account information — {rounded.md} (16px). Role/Division are
             no longer repeated here as text; the hero above is the single
             place that shows them (as badges), so this card stays focused
             on the fields the user can actually edit.
        ──────────────────────────────────────────────────────────── --}}
        <section class="bg-canvas dark:bg-white/[0.04] rounded-md border border-hairline dark:border-white/10 p-xl">
            <div class="flex items-center justify-between mb-lg">
                <div>
                    <h2 class="text-heading-md text-ink dark:text-on-dark">Informasi Akun</h2>
                    <p class="text-body-sm text-mute mt-xxs">Data ini terlihat oleh rekan tim di seluruh sistem helpdesk.</p>
                </div>
                <span class="text-heading-md">👤</span>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col gap-lg">
                @csrf
                @method('PATCH')

                {{-- Hidden — the real file input lives here; the camera badge on the
                     avatar above is its visible trigger, keeping this form uncluttered. --}}
                <input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden"
                       onchange="this.form.requestSubmit()">
                @error('avatar')
                    <p class="field-error -mt-md">{{ $message }}</p>
                @enderror

                @if($user->avatar)
                    <label class="flex items-center gap-xs text-body-sm text-mute select-none w-fit">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded-sm border-ash">
                        Hapus foto profil saat menyimpan
                    </label>
                @endif

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
                               placeholder="08xxxxxxxxxx"
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
                            <option value="id" @selected(old('locale', $user->locale) === 'id')>🇮🇩 Bahasa Indonesia</option>
                            <option value="en" @selected(old('locale', $user->locale) === 'en')>🇬🇧 English</option>
                        </select>
                    </div>

                    <div>
                        <label for="theme" class="field-label">Tema</label>
                        {{--
                            "Fungsikan mode": sebelumnya dropdown ini cuma tersimpan
                            ke database dan baru terlihat efeknya setelah reload —
                            tidak sinkron dengan tombol toggle 🌙/☀️ di header (yang
                            langsung mengubah class + localStorage). Sekarang keduanya
                            memakai fungsi yang sama (window.helpdeskSetTheme), jadi
                            ganti pilihan di sini langsung mengubah tampilan saat itu
                            juga, dan tetap tersimpan permanen saat form disimpan.
                        --}}
                        <select id="theme" name="theme" class="field-input" onchange="window.helpdeskSetTheme(this.value)">
                            <option value="light" @selected(old('theme', $user->theme) === 'light')>☀️ Terang (Light)</option>
                            <option value="dark" @selected(old('theme', $user->theme) === 'dark')>🌙 Gelap (Dark)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-lg border-t border-hairline dark:border-white/10">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </section>

        {{-- Change password --}}
<section class="bg-canvas dark:bg-black/20 rounded-md border border-hairline dark:border-white/10 p-xl">
    <h2 class="text-heading-md text-ink dark:text-on-dark mb-lg">Ubah Kata Sandi</h2>

    <form method="POST" action="{{ route('profile.password.update') }}" class="flex flex-col gap-lg w-full max-w-md">
        @csrf
        @method('PUT')

        <div class="w-full">
            <label for="current_password" class="field-label">Kata Sandi Saat Ini</label>
            <input id="current_password" type="password" name="current_password" required
                   autocomplete="current-password"
                   class="field-input w-full @error('current_password', 'updatePassword') has-error @enderror">
            @error('current_password', 'updatePassword')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="w-full">
            <label for="new_password" class="field-label">Kata Sandi Baru</label>
            <input id="new_password" type="password" name="password" required
                   autocomplete="new-password"
                   class="field-input w-full @error('password', 'updatePassword') has-error @enderror">
            @error('password', 'updatePassword')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="w-full">
            <label for="password_confirmation" class="field-label">Konfirmasi Kata Sandi Baru</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   autocomplete="new-password"
                   class="field-input w-full">
        </div>

        <div>
            <button type="submit" class="btn-secondary">Ubah Kata Sandi</button>
        </div>
    </form>
</section>

        {{-- ────────────────────────────────────────────────────────────
             Notification preferences — grouped into a scannable list with
             per-row dividers and a small icon chip per notification type.
        ──────────────────────────────────────────────────────────── --}}
        <section class="bg-canvas dark:bg-white/[0.04] rounded-md border border-hairline dark:border-white/10 p-xl">
            <div class="flex items-center justify-between mb-lg">
                <div>
                    <h2 class="text-heading-md text-ink dark:text-on-dark">Preferensi Notifikasi</h2>
                    <p class="text-body-sm text-mute mt-xxs">Atur kanal notifikasi per jenis kejadian.</p>
                </div>
                <span class="text-heading-md">🔔</span>
            </div>

            <form method="POST" action="{{ route('profile.notifications.update') }}" class="flex flex-col">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-[1fr_auto_auto] gap-md items-center text-caption-md text-ash uppercase tracking-wide font-semibold pb-sm">
                    <span>Jenis Notifikasi</span>
                    <span class="text-center w-14">In-App</span>
                    <span class="text-center w-14">Email</span>
                </div>

                @php($settings = auth()->user()->notificationSettings->keyBy('type'))
                @foreach (\App\Enums\NotificationType::cases() as $type)
                    @php($setting = $settings->get($type->value))
                    <div class="grid grid-cols-[1fr_auto_auto] gap-md items-center py-md border-t border-hairline dark:border-white/10">
                        <div class="flex items-center gap-sm min-w-0">
                            <span class="w-9 h-9 rounded-full bg-surface-card dark:bg-white/10 flex items-center justify-center text-body-sm shrink-0">
                                {{ $type->icon() }}
                            </span>
                            <span class="text-body-sm text-ink dark:text-on-dark truncate">{{ $type->label() }}</span>
                        </div>
                        <label class="flex justify-center w-14">
                            <input type="checkbox" name="in_app[{{ $type->value }}]" value="1"
                                   @checked($setting?->in_app ?? true) class="rounded-sm border-ash text-primary">
                        </label>
                        <label class="flex justify-center w-14">
                            <input type="checkbox" name="email[{{ $type->value }}]" value="1"
                                   @checked($setting?->email ?? true) class="rounded-sm border-ash text-primary">
                        </label>
                    </div>
                @endforeach

                <div class="pt-lg">
                    <button type="submit" class="btn-secondary">Simpan Preferensi</button>
                </div>
            </form>
        </section>
    </div>

    @push('scripts')
    <script>
        // Shared with the header 🌙/☀️ toggle in layouts/app.blade.php so both
        // controls agree on the current theme immediately (no reload needed).
        window.helpdeskSetTheme = function (value) {
            var isDark = value === 'dark';
            document.documentElement.classList.toggle('dark', isDark);
            localStorage.setItem('helpdesk-theme', isDark ? 'dark' : 'light');
        };
    </script>
    @endpush
@endsection