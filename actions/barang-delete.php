<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('pages/barang.php');

$stmt = db()->prepare('SELECT nama FROM barang WHERE id = ?');
$stmt->execute([$id]);
$barang = $stmt->fetch();
if (!$barang) { set_flash('error', 'Barang tidak ditemukan.'); redirect('pages/barang.php'); }

// Cek relasi
$cek = db()->prepare('SELECT COUNT(*) FROM barang_masuk WHERE barang_id = ?');
$cek->execute([$id]);
$masuk_count = (int)$cek->fetchColumn();
$cek = db()->prepare('SELECT COUNT(*) FROM barang_keluar WHERE barang_id = ?');
$cek->execute([$id]);
$keluar_count = (int)$cek->fetchColumn();

if ($masuk_count > 0 || $keluar_count > 0) {
    set_flash('error', 'Barang tidak dapat dihapus karena memiliki riwayat transaksi. Pertimbangkan untuk mengubah stok menjadi 0.');
    redirect('pages/barang.php');
}

$stmt = db()->prepare('DELETE FROM barang WHERE id = ?');
$stmt->execute([$id]);
log_aktivitas('Hapus', 'Barang', 'Menghapus barang: ' . $barang['nama']);
set_flash('success', 'Barang berhasil dihapus.');
redirect('pages/barang.php');
