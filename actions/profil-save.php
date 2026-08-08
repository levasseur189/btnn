<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/pengaturan.php');

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$no_telepon = trim($_POST['no_telepon'] ?? '');

if ($nama === '') { set_flash('error', 'Nama wajib diisi.'); redirect('pages/pengaturan.php'); }

$foto_path = null;
if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto_path = upload_file($_FILES['foto'], 'profil', ALLOWED_IMAGE_EXT);
    if ($foto_path === null) {
        set_flash('error', 'Foto gagal diupload. Pastikan format JPG/PNG/GIF dan ukuran maksimal 2MB.');
        redirect('pages/pengaturan.php');
    }
}

if ($foto_path !== null) {
    $stmt = db()->prepare('UPDATE admin SET nama=?, email=?, no_telepon=?, foto=? WHERE id=?');
    $stmt->execute([$nama, $email, $no_telepon, $foto_path, $_SESSION['admin_id']]);
} else {
    $stmt = db()->prepare('UPDATE admin SET nama=?, email=?, no_telepon=? WHERE id=?');
    $stmt->execute([$nama, $email, $no_telepon, $_SESSION['admin_id']]);
}
$_SESSION['admin_nama'] = $nama;
log_aktivitas('Edit', 'Pengaturan', 'Mengedit profil admin: ' . $nama);
set_flash('success', 'Profil berhasil diperbarui.');
redirect('pages/pengaturan.php');
