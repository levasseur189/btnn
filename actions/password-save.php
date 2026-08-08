<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('pages/pengaturan.php');

$lama = $_POST['password_lama'] ?? '';
$baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['password_konfirmasi'] ?? '';

if ($lama === '' || $baru === '' || $konfirmasi === '') {
    set_flash('error', 'Semua kolom password wajib diisi.');
    redirect('pages/pengaturan.php#password');
}
if (strlen($baru) < 6) {
    set_flash('error', 'Password baru minimal 6 karakter.');
    redirect('pages/pengaturan.php#password');
}
if ($baru !== $konfirmasi) {
    set_flash('error', 'Konfirmasi password tidak cocok.');
    redirect('pages/pengaturan.php#password');
}

$stmt = db()->prepare('SELECT password FROM admin WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
$hash = $stmt->fetchColumn();

if (!password_verify($lama, $hash)) {
    set_flash('error', 'Password lama salah.');
    redirect('pages/pengaturan.php#password');
}

$new_hash = password_hash($baru, PASSWORD_DEFAULT);
$stmt = db()->prepare('UPDATE admin SET password=? WHERE id=?');
$stmt->execute([$new_hash, $_SESSION['admin_id']]);
log_aktivitas('Edit', 'Pengaturan', 'Mengganti password');
set_flash('success', 'Password berhasil diubah.');
redirect('pages/pengaturan.php#password');
