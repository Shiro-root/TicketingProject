# Helpdesk Enterprise — Modul 4: Ticket Management

Lanjutan dari Modul 1-3 (Database, Authentication, Dashboard). Modul ini adalah **inti
aplikasi**: CRUD ticket lengkap, status workflow 8-tahap, assignment teknisi, comment
system (reply, edit, delete, mention, attachment), internal note, activity timeline
otomatis, dan sejumlah fitur bonus (Duplicate Detection, Bookmark, Watcher).

**Tidak ada migration baru** — seluruh skema sudah tersedia dari Modul 1.

---

## Isi Paket

```
app/Services/TicketService.php                    → business logic inti: create, update, assign,
                                                      transisi status, merge, duplicate, archive, rate
app/Services/TicketActivityService.php             → satu-satunya penulis ke ticket_activities (timeline)
app/Services/TicketCommentService.php              → comment, reply, edit, delete, parsing @mention

app/Http/Controllers/TicketController.php          → CRUD + seluruh aksi workflow
app/Http/Controllers/TicketCommentController.php   → comment & reply
app/Http/Controllers/TicketAttachmentController.php→ download & hapus lampiran
app/Http/Controllers/UserLookupController.php      → AJAX lookup untuk mention & assign picker

app/Http/Requests/Ticket/StoreTicketRequest.php
app/Http/Requests/Ticket/UpdateTicketRequest.php
app/Http/Requests/Ticket/AssignTicketRequest.php
app/Http/Requests/Ticket/TransitionStatusRequest.php
app/Http/Requests/Ticket/StoreCommentRequest.php

app/Policies/TicketPolicy.php                      → otorisasi per-record (baru dibutuhkan mulai modul ini)
app/Http/Resources/TicketResource.php              → JSON response utk AJAX/API (baru dibutuhkan mulai modul ini)
app/Http/Resources/UserLookupResource.php

app/Providers/AppServiceProvider.php               → menambahkan Gate::policy(Ticket::class, TicketPolicy::class)

routes/tickets.php                                  → seluruh route ticket (baru)
routes/web.php                                       → ditambah require routes/tickets.php

resources/views/tickets/index.blade.php             → daftar + filter (status, prioritas, kategori,
                                                        department, teknisi, SLA breach, search, tanggal)
resources/views/tickets/create.blade.php
resources/views/tickets/edit.blade.php
resources/views/tickets/_form.blade.php             → form partial dipakai create & edit
resources/views/tickets/show.blade.php              → detail ticket: aksi workflow, assignment,
                                                        comment, timeline, rating
resources/views/tickets/trashed.blade.php           → daftar ticket ter-soft-delete + restore

resources/views/components/comment-item.blade.php   → komponen comment (rekursif utk reply)
resources/views/components/timeline-item.blade.php  → komponen satu baris activity timeline
```

## Cara Pasang

1. Salin/timpa seluruh folder di atas ke project Laravel-mu (hasil Modul 1-3).
2. **Pastikan symlink storage sudah ada** (dari Modul 2) untuk menyimpan attachment:
   ```bash
   php artisan storage:link
   ```
3. Bersihkan cache config/route (karena ada penambahan route & provider):
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
4. Jalankan:
   ```bash
   php artisan serve
   ```
5. Login sebagai teknisi/admin, buka menu **Ticket** di sidebar (link sudah otomatis
   aktif karena `layouts/app.blade.php` dari Modul 2 mengecek `request()->routeIs()` —
   tidak perlu diubah, tapi Anda bisa update label "Segera Hadir" → link aktif kalau mau,
   lihat catatan di bawah).

> **Catatan sidebar:** `layouts/app.blade.php` Modul 2 masih menandai "Tickets" sebagai
> placeholder abu-abu ("Segera Hadir"). Modul ini tidak menimpa file layout tersebut
> supaya tidak merusak pekerjaan Anda kalau sudah dikustomisasi — silakan ganti baris
> placeholder itu manual jadi `<a href="{{ route('tickets.index') }}">` kapan saja.

---

## Yang Sudah Diimplementasikan

### CRUD & Field Ticket
- Ticket Number otomatis format `TCK-{tahun}-{6 digit urut}`, di-generate di
  `TicketService::generateTicketNumber()` (aman dari race condition ringan karena
  dibungkus transaksi DB).
- Seluruh field sesuai spec: Subject, Description, Category, Priority, Department,
  Status, Due Date (dihitung dari SLA kategori/prioritas), SLA, Assigned Technician,
  Asset (many-to-many), Attachment (banyak file).
- Soft Delete + Restore lewat halaman `/tickets/trashed/list`.

### Status Workflow
- Divalidasi lewat `TicketStatus::allowedTransitions()` (sudah ada dari Modul 1) —
  `TicketService::transitionStatus()` melempar `ValidationException` kalau transisi
  tidak sah, sehingga Controller/View tidak pernah bisa memaksa lompat status.
  ```
  Open → Assigned → Accepted → In Progress → Waiting User/Pending Vendor → Resolved → Closed
  ```
  Tombol aksi di halaman detail ticket otomatis menyesuaikan hanya menampilkan
  transisi yang valid dari status saat ini (`$ticket->status->allowedTransitions()`).
- Reopen dari Resolved/Closed juga didukung sesuai enum.

### Assignment
- Assign teknisi utama otomatis memindahkan status `Open → Assigned`.
- Reassign mencatat siapa teknisi lama → baru di timeline.
- Multiple technician didukung lewat tabel pivot `ticket_technician` (flag `is_lead`).

### Activity Timeline
- **100% otomatis** — tidak ada input manual dari user. Setiap aksi (create, assign,
  reassign, accept, status berubah, comment, internal note, attachment, merge,
  duplicate, close, reopen, archive, restore, rating, watcher) memanggil
  `TicketActivityService`, yang menulis deskripsi human-readable Bahasa Indonesia ke
  `ticket_activities`.

### Internal Note
- Checkbox "Catatan internal" pada form comment, **disembunyikan sepenuhnya dari
  Employee/Guest** di dua lapis:
  1. `TicketCommentService::visibleFor()` — query-level filter `is_internal = false`
     untuk role Employee/Guest.
  2. `StoreCommentRequest::authorize()` — Employee/Guest tidak bisa submit
     `is_internal=1` sama sekali meski memaksa lewat request manual.

### Comment System
- Comment, reply (self-referencing `parent_id`, sudah ada dari Modul 1), edit (hanya
  pemilik, menandai `is_edited`), delete (pemilik atau user dengan permission
  `ticket.update`).
- **Mention (`@nama` / `@email`)** — `TicketCommentService::extractMentions()`
  meng-parse pola `@handle` dan mencocokkan ke user asli lewat email/nama, disimpan
  ke pivot `comment_mentions` (tidak ada notifikasi terkirim di modul ini — hook
  notifikasi disiapkan untuk Modul 5).
- Attachment per-comment didukung (kolom `ticket_comment_id` di `ticket_attachments`).

### Attachment
- Validasi MIME: `jpg,jpeg,png,pdf,docx,xlsx,zip`, maks 10MB/file, maks 5 file per
  aksi (create/update/comment).
- Disimpan ke disk `public` (`storage/app/public/ticket-attachments/{ticket_id}/`),
  bisa langsung didownload via route terproteksi (cek `TicketPolicy::view`).

### Policy & Resource (baru mulai modul ini, sesuai keputusan arsitektur project)
- `TicketPolicy` menangani otorisasi per-record: Employee/Guest hanya melihat/mengedit
  ticket miliknya sendiri (edit dibatasi selagi status masih `Open`); Technician
  terbatas pada ticket yang ditugaskan padanya atau timnya; role dengan permission
  `ticket.view_all` (Manager/Supervisor/Admin) melihat semua. Super Admin tetap bypass
  otomatis lewat `Gate::before` dari Modul 2.
- `TicketResource` & `UserLookupResource` disiapkan untuk konsumsi AJAX (dipakai oleh
  duplicate-check, dan siap dipakai front-end lain seperti mention-autocomplete di
  masa depan).

### Filter & Search (di halaman index)
- Status, Prioritas, Kategori, Department, Teknisi, SLA breach (checkbox), rentang
  tanggal, dan free-text search (nomor ticket, judul, nama pembuat, nama teknisi).
- Employee/Guest otomatis hanya melihat ticket miliknya/ditugaskan padanya —
  di-scope di level query `TicketController::index()`, bukan cuma disembunyikan di UI.

### Bonus Feature yang Sudah Aktif di Modul Ini
- **Duplicate Ticket Detection** — saat mengetik judul (≥8 karakter) di form Create,
  JS memanggil `POST /tickets/check-duplicates` yang menjalankan
  `TicketService::findPossibleDuplicates()` (cek kemiripan teks `similar_text()`
  terhadap ticket lain milik user yang sama dalam 7 hari terakhir, threshold 60%).
  Peringatan tampil inline di bawah field deskripsi, **tidak memblokir submit** —
  hanya informasi.
- **Bookmark / Favorite Ticket** — tombol ☆/★ di halaman detail, tabel
  `ticket_bookmarks` (sudah ada dari Modul 1).
- **Watcher/Follower Ticket** — tombol 🔔/🔕, tercatat di timeline saat seseorang
  mulai memantau.
- **Merge Ticket** — form kecil di halaman detail (khusus user dengan permission
  `ticket.merge`), ticket sumber otomatis diarsipkan dan ditandai `merged_into_id`.
- **Duplicate Ticket (aksi manual)** — tombol "Duplikat" membuat salinan ticket baru
  dengan status `Open`, tag disalin, ditandai `duplicate_of_id`.

Fitur bonus lain di spec besar (Auto Escalation, Dashboard real-time, Export Chart,
Saved Filter, Keyboard Shortcut, Bulk Action, Email Template, Scheduled Report,
Announcement Banner, Maintenance Mode, Multi-language) **belum dikerjakan di modul
ini** — beberapa akan masuk natural di Modul 5 (Notification/Auto Escalation) dan
Modul 9 (Bonus Feature) sesuai urutan tahap yang sudah kita sepakati.

---

## Catatan Desain / Keputusan Teknis

- **Kenapa Policy & Resource baru muncul sekarang?** Sesuai keputusan project sejak
  awal — keduanya baru praktis dibutuhkan begitu ada otorisasi per-record (siapa boleh
  lihat/edit ticket siapa) dan endpoint JSON/AJAX pertama (duplicate-check). Modul 1-3
  sengaja tetap lean tanpa keduanya.
- **Kenapa transisi status divalidasi di Service, bukan di Form Request?** Form
  Request (`TransitionStatusRequest`) hanya memvalidasi bahwa value yang dikirim
  adalah salah satu dari `TicketStatus` enum. Validasi *apakah transisi ini valid dari
  status saat ini* butuh akses ke state ticket saat ini, jadi tempatnya di
  `TicketService::transitionStatus()` — konsisten dengan prinsip Service Layer:
  business rule di Service, bukan di Request/Controller.
- **Kenapa `is_sla_breached` di-refresh di setiap transisi status**, bukan hanya lewat
  scheduled job? Supaya status "overdue" langsung akurat begitu user membuka halaman
  ticket, tanpa menunggu cron job. Scheduled job Auto Escalation (Modul 9 / catatan di
  README Modul 1) tetap dibutuhkan untuk kasus ticket yang **tidak pernah dibuka lagi**
  setelah lewat SLA.
- **Mention tidak mengirim notifikasi** di modul ini — hanya disimpan ke
  `comment_mentions`. Trigger email/in-app notification untuk mention, assignment,
  status change, dst. adalah scope Modul 5 (Notification) sesuai urutan tahap.

---

## Verifikasi

```bash
php artisan route:list --name=tickets
```

Uji manual di browser (gunakan akun demo dari Modul 1, password `password`):

1. Login sebagai `employee@helpdesk.test` → buka `/tickets/create` → buat ticket baru
   dengan judul & deskripsi (≥10 karakter) → submit → harus redirect ke halaman detail
   dengan status **Open**.
2. Login sebagai `admin@helpdesk.test` → buka ticket tadi → assign ke
   `technician@helpdesk.test` → status harus otomatis berubah jadi **Assigned**, dan
   muncul entri baru di Aktivitas.
3. Login sebagai `technician@helpdesk.test` → buka ticket → klik **Terima Ticket** →
   status jadi **Accepted**. Coba ubah status ke **In Progress**, lalu **Resolved**.
4. Sebagai teknisi, tambahkan **catatan internal** di kolom komentar (centang
   "Catatan internal") → logout → login sebagai `employee@helpdesk.test` pemilik
   ticket → buka ticket yang sama → pastikan catatan internal **tidak muncul**.
5. Tambahkan komentar biasa dengan mention `@admin` → cek tabel `comment_mentions`
   bertambah baris.
6. Upload lampiran (JPG/PDF) di form comment → klik nama file di komentar → harus
   ter-download.
7. Sebagai `employee`, buat 2 ticket dengan judul mirip dalam waktu berdekatan → saat
   mengetik judul ticket kedua, peringatan duplikat harus muncul.
8. Sebagai admin, klik **Hapus** pada sebuah ticket → cek muncul di
   `/tickets/trashed/list` → klik **Pulihkan** → ticket kembali muncul di daftar utama.
9. Sebagai `manager@helpdesk.test` (role dengan `ticket.view_all`), buka `/tickets` →
   pastikan semua ticket dari semua user terlihat (bukan cuma miliknya).

---

## Selanjutnya

➡️ **Modul 5: Notification** — In-app notification & email notification (trigger:
ticket dibuat, ticket diassign, ticket diupdate, status berubah, comment baru, SLA
hampir habis), memakai tabel `notifications` dan `notification_settings` yang sudah
ada dari Modul 1, plus `EmailTemplate` (sudah ada datanya dari `EmailTemplateSeeder`)
untuk isi email. Auto Escalation (scheduled job untuk SLA breach) juga masuk di sini.

Beri tahu saya jika Modul 4 ini sudah berjalan tanpa error di lokal Anda (ikuti
langkah Verifikasi di atas) supaya saya lanjut ke Modul 5.
