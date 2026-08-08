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
  - QRCode.js (QR code barang)
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
- CRUD Data Barang + QR Code + Detail + Export Excel/PDF
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

## REST API

Endpoint JSON untuk integrasi sistem lain Bank BTN. Autentikasi via API Key (header `X-API-Key`).

```bash
# List barang
curl -H "X-API-Key: YOUR_KEY" http://localhost/ims-btn/api/index.php/barang

# Detail barang
curl -H "X-API-Key: YOUR_KEY" http://localhost/ims-btn/api/index.php/barang?kode=BRG-0001

# Filter stok menipis
curl -H "X-API-Key: YOUR_KEY" http://localhost/ims-btn/api/index.php/barang?status=menipis

# Transaksi masuk (POST)
curl -X POST -H "X-API-Key: YOUR_KEY" -H "Content-Type: application/json" \
  -d '{"barang_id":1,"jumlah":10,"tanggal":"2025-01-15"}' \
  http://localhost/ims-btn/api/index.php/barang-masuk

# Laporan ringkas
curl -H "X-API-Key: YOUR_KEY" "http://localhost/ims-btn/api/index.php/laporan?dari=2025-01-01&sampai=2025-01-31"
```

Generate API Key di halaman **Pengaturan > REST API Key**.

Endpoint tersedia: `barang`, `kategori`, `supplier`, `transaksi/masuk`, `transaksi/keluar`, `laporan`, `barang-masuk` (POST), `barang-keluar` (POST).

## PWA (Progressive Web App)

Aplikasi dapat diinstall sebagai PWA di tablet/HP:
- Buka di Chrome/Edge, klik tombol install di address bar
- Setelah install, berjalan standalone seperti aplikasi native
- Halaman yang sudah dikunjungi tersedia offline (service worker cache)
- Tema warna Bank BTN otomatis diterapkan

Letakkan ikon di `images/icon-192.png` dan `images/icon-512.png` agar PWA tampil sempurna.

## Catatan

- Letakkan logo Bank BTN di `images/logo-btn.png` dan avatar default di `images/avatar-default.png` (jika tidak ada, aplikasi otomatis memakai placeholder).
- Pastikan folder `uploads/` writable (chmod 775).
- Sesuaikan `BASE_URL` di `config/config.php` jika tidak diakses dari `http://localhost/ims-btn`.
