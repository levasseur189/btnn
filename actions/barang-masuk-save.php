<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/barang-masuk.php');

$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$barang_id = (int)($_POST['barang_id'] ?? 0);
$supplier_id = $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;
$jumlah = (int)($_POST['jumlah'] ?? 0);
$nomor_invoice = trim($_POST['nomor_invoice'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');

if (!$barang_id || $jumlah <= 0) {
    set_flash('error', 'Barang dan jumlah wajib diisi dengan benar.');
    redirect('pages/barang-masuk.php');
}

$bukti = null;
if (!empty($_FILES['bukti']['name'])) {
    $bukti = upload_file($_FILES['bukti'], 'bukti-masuk', ALLOWED_FILE_EXT);
}

db()->beginTransaction();
try {
    $stmt = db()->prepare('INSERT INTO barang_masuk (tanggal, barang_id, supplier_id, jumlah, nomor_invoice, bukti, catatan, admin_id) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$tanggal, $barang_id, $supplier_id, $jumlah, $nomor_invoice, $bukti, $catatan, $_SESSION['admin_id']]);

    $stmt = db()->prepare('UPDATE barang SET stok = stok + ? WHERE id = ?');
    $stmt->execute([$jumlah, $barang_id]);

    $bname = db()->prepare('SELECT nama FROM barang WHERE id = ?');
    $bname->execute([$barang_id]);
    $nama = $bname->fetchColumn();
    log_aktivitas('Tambah', 'Barang Masuk', 'Menambah barang masuk: ' . $nama . ' (' . $jumlah . ')');

    db()->commit();
    set_flash('success', 'Barang masuk berhasil disimpan. Stok bertambah ' . format_angka($jumlah) . '.');
} catch (Exception $e) {
    db()->rollBack();
    set_flash('error', 'Gagal menyimpan: ' . $e->getMessage());
}
redirect('pages/barang-masuk.php');
