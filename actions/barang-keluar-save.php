<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/barang-keluar.php');

$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$barang_id = (int)($_POST['barang_id'] ?? 0);
$jumlah = (int)($_POST['jumlah'] ?? 0);
$tujuan = trim($_POST['tujuan'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');

if (!$barang_id || $jumlah <= 0 || $tujuan === '') {
    set_flash('error', 'Barang, jumlah, dan tujuan wajib diisi dengan benar.');
    redirect('pages/barang-keluar.php');
}

// Cek stok cukup
$stmt = db()->prepare('SELECT stok, nama FROM barang WHERE id = ?');
$stmt->execute([$barang_id]);
$barang = $stmt->fetch();
if (!$barang) { set_flash('error', 'Barang tidak ditemukan.'); redirect('pages/barang-keluar.php'); }

if ($jumlah > (int)$barang['stok']) {
    set_flash('error', 'Stok tidak mencukupi. Stok tersedia: ' . format_angka($barang['stok']) . '.');
    redirect('pages/barang-keluar.php');
}

db()->beginTransaction();
try {
    $stmt = db()->prepare('INSERT INTO barang_keluar (tanggal, barang_id, jumlah, tujuan, catatan, admin_id) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$tanggal, $barang_id, $jumlah, $tujuan, $catatan, $_SESSION['admin_id']]);

    $stmt = db()->prepare('UPDATE barang SET stok = stok - ? WHERE id = ?');
    $stmt->execute([$jumlah, $barang_id]);

    log_aktivitas('Tambah', 'Barang Keluar', 'Mengeluarkan barang: ' . $barang['nama'] . ' (' . $jumlah . ')');

    db()->commit();
    set_flash('success', 'Barang keluar berhasil disimpan. Stok berkurang ' . format_angka($jumlah) . '.');
} catch (Exception $e) {
    db()->rollBack();
    set_flash('error', 'Gagal menyimpan: ' . $e->getMessage());
}
redirect('pages/barang-keluar.php');
