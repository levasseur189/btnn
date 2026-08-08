<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('pages/supplier.php');

$stmt = db()->prepare('SELECT nama FROM supplier WHERE id = ?');
$stmt->execute([$id]);
$sup = $stmt->fetch();
if (!$sup) { set_flash('error', 'Supplier tidak ditemukan.'); redirect('pages/supplier.php'); }

$cek = db()->prepare('SELECT COUNT(*) FROM barang WHERE supplier_id = ?');
$cek->execute([$id]);
if ((int)$cek->fetchColumn() > 0) {
    set_flash('error', 'Supplier tidak dapat dihapus karena masih terkait dengan barang.');
    redirect('pages/supplier.php');
}

$stmt = db()->prepare('DELETE FROM supplier WHERE id = ?');
$stmt->execute([$id]);
log_aktivitas('Hapus', 'Supplier', 'Menghapus supplier: ' . $sup['nama']);
set_flash('success', 'Supplier dihapus.');
redirect('pages/supplier.php');
