-- Migration 0001: Skema awal IMS BTN
-- Membuat seluruh tabel dasar dan data dummy.
-- Jika database sudah ada dari schema.sql, migration ini aman di-skip
-- karena menggunakan IF NOT EXISTS.
-- Catatan: jalankan `schema.sql` terlebih dahulu jika database masih kosong,
-- lalu gunakan sistem migration untuk perubahan skema selanjutnya.

-- Tabel _migrations dibuat otomatis oleh migrate.php

CREATE TABLE IF NOT EXISTS admin (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS kategori (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  kode VARCHAR(20) NOT NULL UNIQUE,
  deskripsi TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  alamat TEXT,
  no_telepon VARCHAR(20),
  email VARCHAR(100),
  kontak_person VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS barang (
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
  KEY idx_supplier (supplier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS barang_masuk (
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
  KEY idx_barang (barang_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS barang_keluar (
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
  KEY idx_barang (barang_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS aktivitas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  admin_id INT UNSIGNED NOT NULL,
  aksi VARCHAR(50) NOT NULL,
  modul VARCHAR(50) NOT NULL,
  keterangan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign keys (dibuat terpisah agar IF NOT EXISTS tidak error)
-- Catatan: MySQL tidak mendukung IF NOT EXISTS untuk FK,
-- jaba ini hanya berjalan sekali. Untuk upgrade, gunakan migration baru.

-- Index untuk pencarian full-text (fitur baru)
CREATE FULLTEXT INDEX IF NOT EXISTS ft_barang_nama ON barang (nama);
CREATE FULLTEXT INDEX IF NOT EXISTS ft_supplier_nama ON supplier (nama);
