-- Migration 0002: Tambah kolom harga dan index untuk pencarian cepat
-- Menambahkan kolom harga_beli, harga_jual pada barang
-- dan index pada tanggal transaksi untuk performa laporan.

-- Tambah kolom harga (nullable, tidak wajib)
ALTER TABLE barang ADD COLUMN IF NOT EXISTS harga_beli DECIMAL(15,2) DEFAULT 0;
ALTER TABLE barang ADD COLUMN IF NOT EXISTS harga_jual DECIMAL(15,2) DEFAULT 0;

-- Index untuk performa laporan per tanggal
CREATE INDEX IF NOT EXISTS idx_masuk_tanggal_full ON barang_masuk (tanggal, barang_id);
CREATE INDEX IF NOT EXISTS idx_keluar_tanggal_full ON barang_keluar (tanggal, barang_id);

-- Index untuk filter status stok cepat
CREATE INDEX IF NOT EXISTS idx_barang_stok_status ON barang (stok, minimal_stok);
