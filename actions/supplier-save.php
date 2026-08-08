<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/supplier.php');

$id = (int)($_POST['id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$no_telepon = trim($_POST['no_telepon'] ?? '');
$email = trim($_POST['email'] ?? '');
$kontak_person = trim($_POST['kontak_person'] ?? '');

if ($nama === '') { set_flash('error', 'Nama supplier wajib diisi.'); redirect('pages/supplier.php'); }

if ($id) {
    $stmt = db()->prepare('UPDATE supplier SET nama=?, alamat=?, no_telepon=?, email=?, kontak_person=? WHERE id=?');
    $stmt->execute([$nama, $alamat, $no_telepon, $email, $kontak_person, $id]);
    log_aktivitas('Edit', 'Supplier', 'Mengedit supplier: ' . $nama);
    set_flash('success', 'Supplier diperbarui.');
} else {
    $stmt = db()->prepare('INSERT INTO supplier (nama, alamat, no_telepon, email, kontak_person) VALUES (?,?,?,?,?)');
    $stmt->execute([$nama, $alamat, $no_telepon, $email, $kontak_person]);
    log_aktivitas('Tambah', 'Supplier', 'Menambah supplier: ' . $nama);
    set_flash('success', 'Supplier ditambahkan.');
}
redirect('pages/supplier.php');
