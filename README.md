# Inventory Management System - Bank BTN

Aplikasi web manajemen inventaris gudang Bank BTN, dibangun dengan **PHP Native + MySQL**.

## Teknologi

- **Frontend:** HTML5, CSS3, Bootstrap 5, Bootstrap Icons, JavaScript
- **Backend:** PHP Native (tanpa framework)
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Library:**
  - SweetAlert2 (notifikasi)
  - DataTables (tabel interaktif)
  - Chart.js (grafik)
  - PhpSpreadsheet (export Excel)
  - DomPDF (export PDF)

## Struktur Folder

```
ims-btn/
├── assets/           # Aset tambahan
├── css/              # Stylesheet (style.css)
├── js/               # JavaScript (app.js)
├── images/           # Logo & gambar statis
├── uploads/          # File upload (foto barang, bukti)
│   ├── barang/
│   └── bukti-masuk/
├── config/           # Konfigurasi (config.php, database.php)
├── includes/         # Header, footer, sidebar, navbar, auth, functions
├── pages/            # Halaman utama (dashboard, barang, dll)
├── modules/          # Modul tambahan (opsional)
├── actions/          # Handler aksi (save, delete, dll)
├── export/           # Export Excel & PDF
├── database/         # Skema SQL + data dummy + migrations/
├── api/              # REST API endpoint JSON
├── vendor/           # Composer dependencies
├── login.php         # Halaman login
├── logout.php        # Logout
├── index.php         # Entry point
├── manifest.json     # PWA manifest
├── sw.js             # Service Worker (PWA offline)
├── offline.html      # Halaman offline fallback
└── composer.json
```

## Instalasi

1. **Salin folder** ke web server (XAMPP/Laragon): `htdocs/ims-btn`
2. **Import database:** buka phpMyAdmin, import `database/schema.sql` (akan membuat database `ims_btn` + data dummy)
3. **Install dependency:**
   ```bash
   composer install
   ```
4. **Konfigurasi koneksi:** edit `config/config.php` (DB_HOST, DB_USER, DB_PASS) sesuai server MySQL Anda
5. **Akses aplikasi:** buka `http://localhost/ims-btn`

## Login Default

| Username | Password  |
|----------|-----------|
| admin01  | admin123  |
| admin02  | admin123  |

> Password di-hash menggunakan `password_hash()`. Untuk reset, generate hash baru dengan `php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"`.

## Fitur

- Dashboard dengan kartu statistik + grafik (Chart.js)
- CRUD Data Barang + Detail + Export Excel/PDF
- CRUD Kategori & Supplier
- Barang Masuk (stok bertambah otomatis, upload bukti)
- Barang Keluar (stok berkurang otomatis, validasi stok)
- Laporan transaksi (harian/mingguan/bulanan/tahunan/custom) + Print + Export
- Pencarian barang di navbar
- Pengaturan: Edit Profil, Ganti Password, Backup & Restore Database, API Key, Migration Status
- **Filter & Sort lanjutan** di DataTables (filter per kategori/supplier/status stok)
- **Database Migration** versi skema (`database/migrations/` + `database/migrate.php`)
- **REST API** JSON untuk integrasi sistem lain (`api/index.php`)
- **PWA** - Progressive Web App, bisa diinstall & dipakai offline di tablet gudang
- Keamanan: Session login, password hashing, prepared statement (PDO), validasi upload, auto-logout sesi

## Database Migration

Sistem migration memungkinkan upgrade skema database tanpa kehilangan data.

```bash
# Jalankan via CLI
php database/migrate.php

# Atau via browser
http://localhost/ims-btn/database/migrate.php?run=1
```

Migration tersimpan di `database/migrations/` dengan format `NNNN_deskripsi.sql`. Tabel `_migrations` mencatat yang sudah dijalankan. Untuk menambah migration baru, buat file SQL baru dengan nomor urut berikutnya.

## Catatan

- Letakkan logo Bank BTN di `images/logo-btn.png` dan avatar default di `images/avatar-default.png` (jika tidak ada, aplikasi otomatis memakai placeholder).
- Pastikan folder `uploads/` writable (chmod 775).
- Sesuaikan `BASE_URL` di `config/config.php` jika tidak diakses dari `http://localhost/ims-btn`.
