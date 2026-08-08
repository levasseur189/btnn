<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/barang.php');

$id = (int)($_POST['id'] ?? 0);
$kode = trim($_POST['kode'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$kategori_id = (int)($_POST['kategori_id'] ?? 0);
$supplier_id = $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null;
$lokasi_rak = trim($_POST['lokasi_rak'] ?? '');
$satuan = trim($_POST['satuan'] ?? '');
$stok = (int)($_POST['stok'] ?? 0);
$minimal_stok = (int)($_POST['minimal_stok'] ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');

$errors = [];
if ($kode === '') $errors[] = 'Kode wajib diisi.';
if ($nama === '') $errors[] = 'Nama wajib diisi.';
if ($kategori_id === 0) $errors[] = 'Kategori wajib dipilih.';
if ($satuan === '') $errors[] = 'Satuan wajib diisi.';

// Cek kode unik
if ($kode) {
    $stmt = db()->prepare('SELECT id FROM barang WHERE kode = ?' . ($id ? ' AND id <> ?' : ''));
    $stmt->execute($id ? [$kode, $id] : [$kode]);
    if ($stmt->fetch()) $errors[] = 'Kode barang sudah digunakan.';
}

if ($errors) {
    set_flash('error', implode(' ', $errors));
    redirect('pages/barang-form.php' . ($id ? "?id=$id" : ''));
}

$foto = null;
if (!empty($_FILES['foto']['name'])) {
    $foto = upload_file($_FILES['foto'], 'barang', ALLOWED_IMAGE_EXT);
    if (!$foto) { set_flash('error', 'Upload foto gagal. Periksa format & ukuran.'); redirect('pages/barang-form.php' . ($id ? "?id=$id" : '')); }
}

if ($id) {
    $stmt = db()->prepare('UPDATE barang SET kode=?, nama=?, kategori_id=?, supplier_id=?, lokasi_rak=?, satuan=?, minimal_stok=?, deskripsi=?' . ($foto ? ', foto=?' : '') . ' WHERE id=?');
    $params = [$kode, $nama, $kategori_id, $supplier_id, $lokasi_rak, $satuan, $minimal_stok, $deskripsi];
    if ($foto) $params[] = $foto;
    $params[] = $id;
    $stmt->execute($params);
    log_aktivitas('Edit', 'Barang', 'Mengedit barang: ' . $nama);
    set_flash('success', 'Barang berhasil diperbarui.');
} else {
    $stmt = db()->prepare('INSERT INTO barang (kode, nama, kategori_id, supplier_id, lokasi_rak, satuan, stok, minimal_stok, foto, qr_code, deskripsi) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$kode, $nama, $kategori_id, $supplier_id, $lokasi_rak, $satuan, $stok, $minimal_stok, $foto, $kode, $deskripsi]);
    log_aktivitas('Tambah', 'Barang', 'Menambah barang: ' . $nama);
    set_flash('success', 'Barang berhasil ditambahkan.');
}
redirect('pages/barang.php');
