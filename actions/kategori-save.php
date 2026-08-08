<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/kategori.php');

$id = (int)($_POST['id'] ?? 0);
$kode = trim($_POST['kode'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

if ($kode === '' || $nama === '') {
    set_flash('error', 'Kode dan nama wajib diisi.');
    redirect('pages/kategori.php');
}

$stmt = db()->prepare('SELECT id FROM kategori WHERE kode = ?' . ($id ? ' AND id <> ?' : ''));
$stmt->execute($id ? [$kode, $id] : [$kode]);
if ($stmt->fetch()) { set_flash('error', 'Kode kategori sudah digunakan.'); redirect('pages/kategori.php'); }

if ($id) {
    $stmt = db()->prepare('UPDATE kategori SET kode=?, nama=?, deskripsi=? WHERE id=?');
    $stmt->execute([$kode, $nama, $deskripsi, $id]);
    log_aktivitas('Edit', 'Kategori', 'Mengedit kategori: ' . $nama);
    set_flash('success', 'Kategori diperbarui.');
} else {
    $stmt = db()->prepare('INSERT INTO kategori (kode, nama, deskripsi) VALUES (?,?,?)');
    $stmt->execute([$kode, $nama, $deskripsi]);
    log_aktivitas('Tambah', 'Kategori', 'Menambah kategori: ' . $nama);
    set_flash('success', 'Kategori ditambahkan.');
}
redirect('pages/kategori.php');
