<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('pages/kategori.php');

$stmt = db()->prepare('SELECT nama FROM kategori WHERE id = ?');
$stmt->execute([$id]);
$kat = $stmt->fetch();
if (!$kat) { set_flash('error', 'Kategori tidak ditemukan.'); redirect('pages/kategori.php'); }

$cek = db()->prepare('SELECT COUNT(*) FROM barang WHERE kategori_id = ?');
$cek->execute([$id]);
if ((int)$cek->fetchColumn() > 0) {
    set_flash('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh barang.');
    redirect('pages/kategori.php');
}

$stmt = db()->prepare('DELETE FROM kategori WHERE id = ?');
$stmt->execute([$id]);
log_aktivitas('Hapus', 'Kategori', 'Menghapus kategori: ' . $kat['nama']);
set_flash('success', 'Kategori dihapus.');
redirect('pages/kategori.php');
