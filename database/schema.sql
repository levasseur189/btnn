-- =====================================================================
-- Inventory Management System - Bank BTN
-- Database Schema + Dummy Data
-- Engine: MySQL 5.7+ / MariaDB 10.3+
-- =====================================================================

DROP DATABASE IF EXISTS ims_btn;
CREATE DATABASE ims_btn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ims_btn;

-- ---------------------------------------------------------------------
-- admin
-- ---------------------------------------------------------------------
CREATE TABLE admin (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  foto VARCHAR(255) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL,
  no_telepon VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- kategori
-- ---------------------------------------------------------------------
CREATE TABLE kategori (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  kode VARCHAR(20) NOT NULL UNIQUE,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- supplier
-- ---------------------------------------------------------------------
CREATE TABLE supplier (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  alamat TEXT,
  no_telepon VARCHAR(20),
  email VARCHAR(100),
  kontak_person VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- barang
-- ---------------------------------------------------------------------
CREATE TABLE barang (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  kategori_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED DEFAULT NULL,
  lokasi_rak VARCHAR(50),
  satuan VARCHAR(20) NOT NULL DEFAULT 'pcs',
  stok INT NOT NULL DEFAULT 0,
  minimal_stok INT NOT NULL DEFAULT 5,
  foto VARCHAR(255) DEFAULT NULL,
  qr_code VARCHAR(255) DEFAULT NULL,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_kode (kode),
  KEY idx_kategori (kategori_id),
  KEY idx_supplier (supplier_id),
  CONSTRAINT fk_barang_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT,
  CONSTRAINT fk_barang_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- barang_masuk
-- ---------------------------------------------------------------------
CREATE TABLE barang_masuk (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tanggal DATE NOT NULL,
  barang_id INT UNSIGNED NOT NULL,
  supplier_id INT UNSIGNED DEFAULT NULL,
  jumlah INT NOT NULL,
  nomor_invoice VARCHAR(50),
  bukti VARCHAR(255) DEFAULT NULL,
  catatan TEXT,
  admin_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tanggal (tanggal),
  KEY idx_barang (barang_id),
  CONSTRAINT fk_masuk_barang FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE RESTRICT,
  CONSTRAINT fk_masuk_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(id) ON DELETE SET NULL,
  CONSTRAINT fk_masuk_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- barang_keluar
-- ---------------------------------------------------------------------
CREATE TABLE barang_keluar (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tanggal DATE NOT NULL,
  barang_id INT UNSIGNED NOT NULL,
  jumlah INT NOT NULL,
  tujuan VARCHAR(150),
  catatan TEXT,
  admin_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tanggal (tanggal),
  KEY idx_barang (barang_id),
  CONSTRAINT fk_keluar_barang FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE RESTRICT,
  CONSTRAINT fk_keluar_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- aktivitas
-- ---------------------------------------------------------------------
CREATE TABLE aktivitas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id INT UNSIGNED NOT NULL,
  aksi VARCHAR(50) NOT NULL,
  modul VARCHAR(50) NOT NULL,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin (admin_id),
  CONSTRAINT fk_aktivitas_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- DUMMY DATA
-- =====================================================================

-- Admin (password: admin123) - hashed with password_hash() PASSWORD_DEFAULT
-- password: admin123  (generated with password_hash('admin123', PASSWORD_DEFAULT) on PHP 8+)
INSERT INTO admin (nama, username, password, email, no_telepon) VALUES
('Andi Pratama', 'admin01', '$2y$12$9W70bb4EXHxyoA9fAtZl5.HE9s8zHwB7hgPuhdBEYUkLrDbhIaNUm', 'andi.pratama@btn.co.id', '081234567801'),
('Budi Santoso', 'admin02', '$2y$12$9W70bb4EXHxyoA9fAtZl5.HE9s8zHwB7hgPuhdBEYUkLrDbhIaNUm', 'budi.santoso@btn.co.id', '081234567802');

-- Kategori
INSERT INTO kategori (nama, kode, deskripsi) VALUES
('ATK', 'KAT-ATK', 'Alat Tulis Kantor'),
('Elektronik', 'KAT-ELK', 'Peralatan Elektronik'),
('Furnitur', 'KAT-FUR', 'Perabot Kantor'),
('Kebersihan', 'KAT-KBR', 'Peralatan Kebersihan'),
('IT & Jaringan', 'KAT-ITJ', 'Peralatan IT dan Jaringan'),
('Keamanan', 'KAT-KMN', 'Peralatan Keamanan');

-- Supplier
INSERT INTO supplier (nama, alamat, no_telepon, email, kontak_person) VALUES
('PT Sumber Rezeki', 'Jl. Industri No. 12, Jakarta Utara', '021-5551234', 'sales@sumberrezeki.co.id', 'Hendra Wijaya'),
('CV Mitra Elektronik', 'Jl. Mangga Dua No. 45, Jakarta Pusat', '021-5555678', 'info@mitraelektronik.com', 'Sri Lestari'),
('PT Office Supply', 'Jl. Sudirman Kav. 22, Jakarta Selatan', '021-5559012', 'cs@officesupply.id', 'Rudi Hartono'),
('PT Furnitur Jaya', 'Jl. Daan Mogot No. 88, Jakarta Barat', '021-5553456', 'admin@furniturjaya.co.id', 'Maya Sari'),
('CV Bersih Sehat', 'Jl. Cipinang No. 10, Jakarta Timur', '021-5557890', 'order@bersihsehat.com', 'Agus Salim');

-- Barang
INSERT INTO barang (kode, nama, kategori_id, supplier_id, lokasi_rak, satuan, stok, minimal_stok, foto, qr_code, deskripsi) VALUES
('BRG-0001', 'Kertas A4 70 GSM', 1, 3, 'Rak A-01', 'rim', 120, 20, NULL, 'BRG-0001', 'Kertas HVS A4 70 GSM untuk keperluan kantor'),
('BRG-0002', 'Pulpen Standard AE7', 1, 3, 'Rak A-02', 'pcs', 15, 30, NULL, 'BRG-0002', 'Pulpen tinta biru'),
('BRG-0003', 'Tinta Printer HP 680', 2, 2, 'Rak B-01', 'pcs', 0, 5, NULL, 'BRG-0003', 'Cartridge printer HP hitam'),
('BRG-0004', 'Laptop Dell Latitude', 5, 2, 'Rak C-01', 'unit', 8, 3, NULL, 'BRG-0004', 'Laptop kerja Dell Latitude 5420'),
('BRG-0005', 'Kursi Ergonomis', 3, 4, 'Gudang G-01', 'pcs', 25, 5, NULL, 'BRG-0005', 'Kursi kantor ergonomis'),
('BRG-0006', 'Meja Kerja', 3, 4, 'Gudang G-02', 'pcs', 14, 4, NULL, 'BRG-0006', 'Meja kerja kantor'),
('BRG-0007', 'Sabun Cair 5L', 4, 5, 'Rak D-01', 'galon', 6, 3, NULL, 'BRG-0007', 'Sabun cair tangan 5 liter'),
('BRG-0008', 'Kabel UTP Cat6', 5, 2, 'Rak B-02', 'meter', 350, 50, NULL, 'BRG-0008', 'Kabel jaringan UTP Cat6'),
('BRG-0009', 'Switch 24 Port', 5, 2, 'Rak B-03', 'pcs', 4, 2, NULL, 'BRG-0009', 'Switch jaringan 24 port gigabit'),
('BRG-0010', 'CCTV Indoor', 6, 2, 'Rak B-04', 'unit', 10, 3, NULL, 'BRG-0010', 'Kamera CCTV indoor 2MP'),
('BRG-0011', 'Map Plastik', 1, 3, 'Rak A-03', 'pcs', 200, 50, NULL, 'BRG-0011', 'Map plastik dokumen'),
('BRG-0012', 'Stapler Standard', 1, 3, 'Rak A-04', 'pcs', 18, 10, NULL, 'BRG-0012', 'Stapler kantor'),
('BRG-0013', 'Lemari Arsip', 3, 4, 'Gudang G-03', 'unit', 7, 2, NULL, 'BRG-0013', 'Lemari arsip besi 4 pintu'),
('BRG-0014', 'Sapu Lantai', 4, 5, 'Rak D-02', 'pcs', 22, 8, NULL, 'BRG-0014', 'Sapu lantai serbaguna'),
('BRG-0015', 'UPS 1000VA', 5, 2, 'Rak B-05', 'unit', 3, 2, NULL, 'BRG-0015', 'UPS 1000VA untuk server'),
('BRG-0016', 'Mouse Wireless', 5, 2, 'Rak B-06', 'pcs', 0, 10, NULL, 'BRG-0016', 'Mouse wireless Logitech'),
('BRG-0017', 'Keyboard USB', 5, 2, 'Rak B-07', 'pcs', 9, 5, NULL, 'BRG-0017', 'Keyboard USB standard'),
('BRG-0018', 'Toner Canon 052', 2, 2, 'Rak B-08', 'pcs', 2, 4, NULL, 'BRG-0018', 'Toner printer Canon hitam'),
('BRG-0019', 'Kertas Foto A4', 1, 3, 'Rak A-05', 'rim', 35, 10, NULL, 'BRG-0019', 'Kertas foto glossy A4'),
('BRG-0020', 'Gembok Brankas', 6, 4, 'Rak E-01', 'pcs', 5, 2, NULL, 'BRG-0020', 'Gembok brankas kuningan');

-- Barang Masuk (last 14 days)
INSERT INTO barang_masuk (tanggal, barang_id, supplier_id, jumlah, nomor_invoice, bukti, catatan, admin_id) VALUES
(CURDATE(), 1, 3, 50, 'INV-2025-0001', NULL, 'Pengisian stok awal bulan', 1),
(CURDATE(), 8, 2, 200, 'INV-2025-0002', NULL, 'Restock kabel UTP', 2),
(CURDATE() - INTERVAL 1 DAY, 5, 4, 10, 'INV-2025-0003', NULL, 'Pembelian kursi baru', 1),
(CURDATE() - INTERVAL 1 DAY, 11, 3, 100, 'INV-2025-0004', NULL, 'Restock map', 2),
(CURDATE() - INTERVAL 2 DAY, 14, 5, 15, 'INV-2025-0005', NULL, 'Pengadaan alat kebersihan', 1),
(CURDATE() - INTERVAL 3 DAY, 4, 2, 3, 'INV-2025-0006', NULL, 'Pengadaan laptop baru', 2),
(CURDATE() - INTERVAL 4 DAY, 17, 2, 5, 'INV-2025-0007', NULL, 'Restock keyboard', 1),
(CURDATE() - INTERVAL 5 DAY, 6, 4, 6, 'INV-2025-0008', NULL, 'Pengadaan meja', 2),
(CURDATE() - INTERVAL 6 DAY, 10, 2, 4, 'INV-2025-0009', NULL, 'Pengadaan CCTV', 1),
(CURDATE() - INTERVAL 7 DAY, 19, 3, 20, 'INV-2025-0010', NULL, 'Restock kertas foto', 2),
(CURDATE() - INTERVAL 10 DAY, 13, 4, 2, 'INV-2025-0011', NULL, 'Pengadaan lemari arsip', 1),
(CURDATE() - INTERVAL 12 DAY, 20, 4, 3, 'INV-2025-0012', NULL, 'Pengadaan gembok', 2);

-- Barang Keluar (last 14 days)
INSERT INTO barang_keluar (tanggal, barang_id, jumlah, tujuan, catatan, admin_id) VALUES
(CURDATE(), 1, 10, 'Divisi Operasional', 'Pemakaian harian', 1),
(CURDATE(), 8, 50, 'Instalasi Jaringan Lt.3', 'Penarikan kabel ruang server', 2),
(CURDATE() - INTERVAL 1 DAY, 2, 5, 'Divisi Pembiayaan', 'Pemakaian harian', 1),
(CURDATE() - INTERVAL 1 DAY, 11, 30, 'Semua Divisi', 'Distribusi map', 2),
(CURDATE() - INTERVAL 2 DAY, 4, 1, 'IT Support', 'Penggantian unit rusak', 1),
(CURDATE() - INTERVAL 3 DAY, 17, 2, 'Customer Service', 'Penggantian keyboard rusak', 2),
(CURDATE() - INTERVAL 4 DAY, 14, 3, 'Cleaning Service', 'Pemakaian harian', 1),
(CURDATE() - INTERVAL 5 DAY, 6, 2, 'Renovasi Lt.2', 'Penambahan meja kerja', 2),
(CURDATE() - INTERVAL 6 DAY, 10, 1, 'Keamanan Lt.1', 'Penggantian CCTV rusak', 1),
(CURDATE() - INTERVAL 8 DAY, 19, 5, 'Marketing', 'Cetak materi promosi', 2),
(CURDATE() - INTERVAL 11 DAY, 5, 3, 'Divisi Baru', 'Pengadaan kursi karyawan baru', 1),
(CURDATE() - INTERVAL 13 DAY, 20, 1, 'Brankas Utama', 'Penggantian gembok', 2);

-- Aktivitas
INSERT INTO aktivitas (admin_id, aksi, modul, keterangan) VALUES
(1, 'Login', 'Auth', 'Admin Andi Pratama login'),
(2, 'Login', 'Auth', 'Admin Budi Santoso login'),
(1, 'Tambah', 'Barang Masuk', 'Menambah barang masuk: Kertas A4 70 GSM (50 rim)'),
(2, 'Tambah', 'Barang Masuk', 'Menambah barang masuk: Kabel UTP Cat6 (200 meter)'),
(1, 'Tambah', 'Barang Keluar', 'Mengeluarkan barang: Kertas A4 70 GSM (10 rim)'),
(2, 'Tambah', 'Barang Keluar', 'Mengeluarkan barang: Kabel UTP Cat6 (50 meter)'),
(1, 'Edit', 'Barang', 'Mengedit barang: Pulpen Standard AE7'),
(2, 'Tambah', 'Supplier', 'Menambah supplier: CV Bersih Sehat'),
(1, 'Tambah', 'Kategori', 'Menambah kategori: Keamanan'),
(2, 'Hapus', 'Barang', 'Menghapus barang kadaluarsa');
