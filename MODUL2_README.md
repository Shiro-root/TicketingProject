# Helpdesk Enterprise — Modul 2: Authentication

Lanjutan dari Modul 1 (Database). Modul ini menambahkan Controller, Form Request,
Middleware RBAC, Service Layer, dan view Blade (mengikuti token desain di `DESIGN.md`)
di atas skema yang sudah ada — **tidak ada migration baru**.

---

## Isi Paket

```
app/Http/Controllers/Auth/AuthenticatedSessionController.php   → login, logout, remember me
app/Http/Controllers/Auth/PasswordResetLinkController.php      → kirim email lupa password
app/Http/Controllers/Auth/NewPasswordController.php            → set password baru
app/Http/Controllers/ProfileController.php                     → profil, avatar, ganti password
app/Http/Requests/Auth/LoginRequest.php                         → validasi + rate limiting login
app/Http/Requests/ProfileUpdateRequest.php
app/Http/Requests/UpdatePasswordRequest.php
app/Http/Middleware/RoleMiddleware.php                          → middleware('role:admin,manager')
app/Http/Middleware/PermissionMiddleware.php                    → middleware('permission:ticket.assign')
app/Http/Middleware/EnsureAccountIsActive.php                   → auto-logout jika status jadi nonaktif
app/Services/AuditLogger.php                                    → service layer utk audit_logs
app/Notifications/ResetPasswordNotification.php                 → email reset password berbahasa Indonesia
app/Providers/AppServiceProvider.php                             → Gate::before (Super Admin bypass semua policy)
bootstrap/app.php                                                → daftar middleware alias (Laravel 11 style)
routes/web.php, routes/auth.php
resources/views/layouts/guest.blade.php                         → chrome untuk halaman login/reset
resources/views/layouts/app.blade.php                           → shell topnav+sidebar utk halaman setelah login
resources/views/auth/{login,forgot-password,reset-password}.blade.php
resources/views/profile/edit.blade.php
resources/views/partials/flash-messages.blade.php
resources/views/dashboard-placeholder.blade.php                 → placeholder, akan ditimpa Modul 3
resources/css/app.css                                            → utility classes turunan token DESIGN.md
tailwind.config.js                                                → seluruh token warna/radius/spacing/typography DESIGN.md
```

## Cara Pasang

1. Salin/timpa seluruh folder di atas ke project Laravel-mu (hasil Modul 1).
2. Install dependency frontend:

   ```bash
   npm install -D tailwindcss @tailwindcss/postcss autoprefixer
   npm install
   ```

   Pastikan `postcss.config.js` memuat plugin Tailwind standar Laravel + Vite (`resources/css/app.css` sudah disiapkan).

3. Buat symlink storage (dipakai untuk avatar):

   ```bash
   php artisan storage:link
   ```

4. **Satu perubahan kecil di `app/Models/User.php`** — override method notifikasi reset
   password supaya memakai template berbahasa Indonesia (`ResetPasswordNotification`)
   alih-alih notifikasi default Laravel:

   ```php
   use App\Notifications\ResetPasswordNotification;
   use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

   // di dalam class User, tambahkan:
   public function sendPasswordResetNotification($token): void
   {
       $this->notify(new ResetPasswordNotification($token));
   }
   ```

5. Konfigurasi `.env` untuk email (dev: gunakan `MAIL_MAILER=log` supaya email reset
   password langsung terlihat di `storage/logs/laravel.log`).

6. Jalankan:

   ```bash
   npm run build   # atau `npm run dev` saat development
   php artisan serve
   ```

7. Login dengan salah satu akun demo dari Modul 1 (password: `password`), contoh
   `superadmin@helpdesk.test`.

---

## Yang Sudah Diimplementasikan

### Login / Logout / Remember Me
- Session-based auth (`Auth::attempt`) dengan cookie "remember me" bawaan Laravel.
- Rate limiting 5x percobaan per kombinasi email+IP (`LoginRequest::ensureIsNotRateLimited`),
  sesuai requirement **Rate Limiting** di bagian Security.
- Akun dengan status `inactive`/`suspended` ditolak saat login meskipun password benar,
  dan otomatis di-logout paksa di tengah sesi jika status berubah setelah login
  (`EnsureAccountIsActive` middleware, dipasang global di grup `web`).
- `last_login_at` & `last_login_ip` di-update setiap login sukses.

### Forgot / Reset Password
- Memakai `password_reset_tokens` (sudah ada dari Modul 1) via `Password` broker bawaan
  Laravel — token di-hash, kedaluwarsa 60 menit (default `config/auth.php`).
- Respons "lupa password" **tidak membocorkan** apakah email terdaftar (mitigasi
  user-enumeration) — baik email valid maupun tidak menampilkan pesan sukses yang sama.

### Change Password
- Form terpisah di halaman profil, wajib memasukkan password lama
  (rule `current_password`) sebelum bisa mengganti password baru.

### Profile & Avatar
- Update nama/email/phone/position/locale/theme.
- Upload avatar (jpg/png/webp, maks 2MB) ke `storage/app/public/avatars`, dengan opsi
  hapus avatar. File lama otomatis dihapus saat diganti/dihapus.
- Mengubah email otomatis me-reset `email_verified_at` (perlu verifikasi ulang jika
  fitur email verification diaktifkan nanti).

### RBAC (Role Based Access Control)
- `role:slug1,slug2` — middleware pembatas per role, contoh:
  `Route::middleware('role:admin,manager')->group(...)`.
- `permission:slug` — middleware pembatas per permission granular (memakai relasi
  `roles ↔ permissions` dari Modul 1), contoh: `middleware('permission:ticket.assign')`.
- `Gate::before()` di `AppServiceProvider` membuat **Super Admin selalu lolos** setiap
  Gate/Policy check ke depannya — konsisten dengan hierarki role di README Modul 1.
- Kedua middleware sudah didaftarkan sebagai alias di `bootstrap/app.php` (gaya Laravel 11,
  tidak ada `app/Http/Kernel.php`).

### Audit Log
- `App\Services\AuditLogger` — service layer tunggal yang dipakai semua controller untuk
  mencatat ke tabel `audit_logs` (login, logout, update profil, ganti password), sesuai
  requirement **Clean Code → Service Layer**. Modul-modul berikutnya (Ticket, Asset, dst.)
  tinggal inject `AuditLogger` yang sama.

### UI/UX — Konsisten dengan `DESIGN.md`
- `tailwind.config.js` memetakan **seluruh** token warna, radius, spacing, dan typography
  scale dari `DESIGN.md` satu-ke-satu (nama token sama persis: `primary`, `surface-card`,
  `rounded-md` = 16px, `rounded-lg` = 32px, `rounded-full` = pill, dst).
- Halaman login/forgot/reset password memakai `{component.modal-card}` (rounded-lg 32px,
  shadow modal 16px ambient) sebagai kartu tengah, tombol primary merah `#e60023`
  (`{component.button-primary}`), input dengan focus-ring biru dua-lapis persis spesifikasi
  `{component.text-input-focused}`.
- Halaman yang **belum ada** di `DESIGN.md` (topnav+sidebar aplikasi setelah login, halaman
  profil) dibangun baru dengan token yang sama (warna, radius, spacing, tipografi Pin Sans/
  Inter) supaya tetap satu sistem visual, sesuai instruksi "buat halaman baru dengan style
  yang konsisten".
- Dark mode: toggle tersimpan di kolom `users.theme`, diterapkan lewat class `dark` di `<html>`
  (sudah disiapkan untuk seluruh sistem, bukan cuma modul ini).

---

## Catatan Desain / Keputusan Teknis

- **Kenapa bukan Sanctum SPA token auth?** README Modul 1 menyebut Sanctum untuk
  persiapan API, tapi aplikasi ini adalah monolith Blade (tidak ada Vue/React di
  `composer.json`). Modul 2 memakai **session auth standar Laravel** (guard `web`) — ini
  yang benar untuk Blade. Sanctum tetap ter-install dan siap dipakai nanti kalau ada
  kebutuhan API/mobile (personal access token), tanpa konflik dengan auth Blade ini.
- **Kenapa `EnsureAccountIsActive` terpisah dari cek status saat login?** Supaya admin
  bisa men-suspend user yang sedang online dan sesi mereka langsung mati di request
  berikutnya, bukan menunggu sampai mereka logout/login ulang.
- **Named error bag `updatePassword`** dipakai di form ganti password supaya validasi
  error-nya tidak tercampur dengan form info profil di halaman yang sama.

---

## Verifikasi

```bash
php artisan route:list --name=login
php artisan route:list --name=profile
php artisan tinker
>>> auth()->attempt(['email' => 'admin@helpdesk.test', 'password' => 'password']);
```

Uji manual di browser:
1. Login dengan `technician@helpdesk.test` / `password` → harus redirect ke `/dashboard`.
2. Coba salah password 6x berturut-turut → harus kena rate limit (pesan "Terlalu banyak percobaan").
3. Buka `/profile`, ganti nama & upload avatar → cek `audit_logs` bertambah baris `update`.
4. Klik "Lupa kata sandi?", cek `storage/logs/laravel.log` (jika `MAIL_MAILER=log`) untuk link reset.
5. Set salah satu user demo jadi `status = suspended` lewat Tinker, lalu refresh halaman
   yang sedang mereka buka → harus otomatis ter-logout ke halaman login.

---

## Selanjutnya

➡️ **Modul 3: Dashboard** — widget total/status ticket, statistik hari/minggu/bulan/tahun,
grafik (per bulan/kategori/prioritas/teknisi), teknisi terbaik, SLA performance, ticket
terbaru & aktivitas terbaru. `resources/views/dashboard-placeholder.blade.php` dan route
`dashboard` di `routes/web.php` akan ditimpa oleh `DashboardController` yang sebenarnya.

Beri tahu saya jika Modul 2 ini sudah berjalan tanpa error di lokal Anda supaya saya lanjut ke Modul 3.
