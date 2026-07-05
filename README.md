
| Role | Email |
|---|---|
| Super Admin | superadmin@helpdesk.test |
| Admin | admin@helpdesk.test |
| Manager | manager@helpdesk.test |
| Supervisor | supervisor@helpdesk.test |
| Technician | technician@helpdesk.test |
| Employee | employee@helpdesk.test |
| Guest | guest@helpdesk.test |

# 🎫 Enterprise Helpdesk Ticketing System

Sistem helpdesk & ticketing enterprise yang dibangun dari nol menggunakan **Laravel 12**, **Tailwind CSS v4**, dan **Alpine.js** — dirancang untuk menyamai kapabilitas Freshdesk, Jira Service Management, dan osTicket dengan arsitektur yang bersih dan mudah dikembangkan.

## ✨ Fitur Utama

- **Multi-Role Authentication (RBAC)** — 7 role: Super Admin, Admin, Manager, Supervisor, Technician, Employee, Guest
- **Ticket Management** — status workflow lengkap (Open → Assigned → Accepted → In Progress → Waiting User → Pending Vendor → Resolved → Closed), merge, duplicate, archive, soft delete + restore
- **SLA Management** — otomatis dihitung per prioritas (Low/Medium/High/Critical), auto-escalation saat SLA terlewati
- **Dashboard Analitik** — statistik real-time, grafik per bulan/kategori/prioritas/teknisi, ranking teknisi terbaik
- **Knowledge Base** — artikel FAQ/Tutorial/SOP/Dokumentasi dengan full-text search dan **AI Suggested Solution**
- **Asset Management** — inventaris perangkat IT terhubung langsung ke ticket
- **Notification System** — in-app & email, dengan preferensi per user
- **Report & Export** — PDF (DomPDF) & Excel (Laravel Excel) dengan filter multi-dimensi
- **Audit Log** — pencatatan penuh setiap aksi (login, create, update, delete, assign, approval, dll)
- **Approval Workflow** — alur persetujuan bertingkat yang bisa dikustomisasi
- **Bonus Features** — Saved Filter, Bulk Action, Bookmark/Watcher Ticket, Duplicate Detection, Dashboard Real-time, Scheduled Report, Announcement Banner, Maintenance Mode, Keyboard Shortcuts

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade, Tailwind CSS v4, Alpine.js, Vite |
| Database | MySQL / SQLite |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/excel |
| Auth | Laravel Sanctum |

## 🏗️ Arsitektur

Dibangun dengan prinsip **Clean Architecture**:
- Service Layer untuk seluruh business logic (Controller tidak menyentuh Eloquent langsung untuk operasi tulis)
- Form Request untuk validasi & otorisasi
- Policy & Gate untuk kontrol akses granular
- Enum untuk Status, Priority, Role, dan konstanta domain lainnya
- Audit Logger terintegrasi di setiap service
- Design token system custom (lihat `DESIGN.md`) untuk konsistensi UI di seluruh modul

## 📦 Progres Pengembangan

Dikerjakan bertahap per modul (dengan dokumentasi `MODULX_README.md` di setiap tahap):

- [x] Modul 1 — Database Schema & Seeders
- [x] Modul 2 — Authentication & RBAC
- [x] Modul 3 — Dashboard
- [x] Modul 4 — Ticket Management
- [x] Modul 5 — Notification System
- [x] Modul 6 — Knowledge Base
- [x] Modul 7 — Asset Management
- [x] Modul 8 — Report & Export
- [ ] Modul 9 — Bonus Features *(in progress)*

## 🚀 Setup Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install && npm run build
```

Akun demo (password: `password`): `superadmin@helpdesk.test`, `admin@helpdesk.test`, `manager@helpdesk.test`, `technician@helpdesk.test`, `employee@helpdesk.test`

## 📄 License

MIT
