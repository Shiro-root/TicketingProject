# Helpdesk Enterprise — Backend (Laravel)

## Tahap 1: Database ✅

Ini adalah hasil **Tahap 1** dari rencana pengerjaan bertahap: struktur database lengkap
(migration, model + relasi, enum, seeder, factory, dummy data) untuk seluruh modul
Enterprise Helpdesk Ticketing System.

Tahap berikutnya (Authentication, Dashboard, Ticket Management, dst.) akan menambahkan
Controller, Form Request, Policy, Resource, Service Layer, dan route di atas fondasi ini —
**tidak perlu migrasi ulang skema di tahap-tahap berikutnya.**

---

## Cara Setup di Lokal

### 1. Buat project Laravel baru (kosong)

```bash
composer create-project laravel/laravel helpdesk-backend "^11.0"
cd helpdesk-backend
```

### 2. Salin file dari paket ini

Salin/timpa folder & file berikut dari paket yang saya berikan ke dalam project Laravel barumu:

```
app/Enums/            → app/Enums/
app/Models/            → app/Models/   (timpa User.php bawaan Laravel)
database/migrations/   → database/migrations/  (HAPUS migration bawaan Laravel yang bentrok:
                          0001_01_01_000000_create_users_table.php,
                          0001_01_01_000001_create_cache_table.php,
                          0001_01_01_000002_create_jobs_table.php
                          — karena sudah digantikan versi custom di paket ini)
database/seeders/      → database/seeders/  (timpa DatabaseSeeder.php bawaan)
database/factories/    → database/factories/ (timpa UserFactory.php bawaan)
.env.example           → gabungkan dengan .env.example bawaan (isi DB_* sesuai lokal)
```

### 3. Install dependency tambahan

```bash
composer require laravel/sanctum
composer require spatie/laravel-medialibrary   # untuk attachment (dipakai di Tahap 4)
composer require barryvdh/laravel-dompdf       # untuk export PDF (Tahap 8)
composer require maatwebsite/excel             # untuk export Excel (Tahap 8)
composer require --dev fakerphp/faker
```

> Package `medialibrary`, `dompdf`, dan `maatwebsite/excel` belum dipakai di Tahap 1 ini —
> disiapkan lebih awal supaya `composer.json` konsisten dengan rencana ke depan. Boleh
> di-skip dulu jika ingin instalasi minimal, tinggal `composer require` lagi nanti saat
> masuk ke tahap yang membutuhkannya.

### 4. Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Buat database MySQL kosong bernama `helpdesk`, lalu sesuaikan `DB_*` di `.env`.

### 5. Migrate & Seed

```bash
php artisan migrate:fresh --seed
```

Jika berhasil, akan muncul tabel akun demo di terminal (password semua: `password`):

| Role | Email |
|---|---|
| Super Admin | superadmin@helpdesk.test |
| Admin | admin@helpdesk.test |
| Manager | manager@helpdesk.test |
| Supervisor | supervisor@helpdesk.test |
| Technician | technician@helpdesk.test |
| Employee | employee@helpdesk.test |
| Guest | guest@helpdesk.test |

### 6. Verifikasi

```bash
php artisan tinker
>>> App\Models\Ticket::count();        // harus ~90
>>> App\Models\User::count();          // harus ~28
>>> App\Models\Ticket::with(['category','assignee','activities'])->first();
```

---

## Struktur Database

### Enum (`app/Enums/`)
| Enum | Kegunaan |
|---|---|
| `TicketStatus` | 10 status + `allowedTransitions()` untuk validasi alur status |
| `TicketPriority` | Low/Medium/High/Critical + `defaultSlaHours()` |
| `UserRole` | 7 role slug (dipakai seeder & policy, sumber kebenaran RBAC tetap di tabel `roles`) |
| `UserStatus`, `AssetStatus`, `AssetType`, `ApprovalStatus`, `ActivityType`, `NotificationType`, `AuditAction` | — |

### Tabel Inti & Relasinya
- **departments** ← users, categories, tickets, assets
- **roles** ↔ **permissions** (many-to-many via `permission_role`) ← users
- **users** — auth utama (Sanctum), punya role & department
- **slas** ← categories, tickets (1 SLA per priority level)
- **categories** — punya department & SLA default
- **tickets** — entitas inti, punya category/department/sla/creator/assignee
  - `ticket_technician` — multi-teknisi (pivot, ada flag `is_lead`)
  - `ticket_tag`, `ticket_asset`, `ticket_watchers`, `ticket_bookmarks` — pivot
  - `ticket_comments` (self-referencing `parent_id` untuk reply, flag `is_internal` untuk catatan internal)
  - `comment_mentions` — tracking @mention
  - `ticket_attachments`
  - `ticket_activities` — timeline otomatis (bukan diisi manual, akan diisi oleh Service Layer di Tahap 4)
- **approval_workflows** → **approval_steps** → **ticket_approvals**
- **audit_logs** — polymorphic (`auditable_type`/`auditable_id`)
- **knowledge_base_categories** → **knowledge_base_articles** (full-text index untuk fitur AI Suggested Solution)
- **assets** — terhubung ke ticket via `ticket_asset`
- **saved_filters**, **announcements**, **email_templates**, **notification_settings**
- **notifications** — tabel default Laravel (channel database)
- Tabel infrastruktur: `sessions`, `personal_access_tokens` (Sanctum), `cache`, `jobs`, `failed_jobs`, `job_batches`

### Catatan Desain Skema
- **FK melingkar** `departments.manager_id → users.id` dan `users.department_id → departments.id`
  diselesaikan dengan migration terpisah (`2024_01_01_000006_add_department_manager_foreign.php`)
  yang menambahkan constraint setelah tabel `users` ada.
- **Soft delete** dipasang di: `users`, `departments`, `categories`, `assets`, `tickets`,
  `ticket_comments`, `knowledge_base_articles` — mendukung fitur *Delete (Soft Delete) + Restore*.
- **SLA breach** dihitung dari `tickets.due_at` vs `now()` — kolom `is_sla_breached` di-cache
  agar query dashboard/report tidak perlu hitung ulang setiap request (akan di-refresh oleh
  scheduled job Auto Escalation di Tahap 9).
- **Ticket status workflow** divalidasi lewat `TicketStatus::allowedTransitions()`, dipakai nanti
  oleh `TicketService` (Tahap 4) supaya status tidak bisa loncat sembarangan dari Controller.

---

## Tahap Selanjutnya

➡️ **Tahap 2: Authentication** — Sanctum SPA auth, login/logout/remember me,
forgot & reset password, change password, profile + avatar upload, middleware RBAC,
Policy dasar per role.

Beri tahu saya jika Tahap 1 ini sudah berhasil di-migrate & di-seed di lokal Anda tanpa
error, supaya saya lanjut ke Tahap 2.
