<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['sql_file']['name'])) {
    set_flash('error', 'File SQL wajib diunggah.');
    redirect('pages/pengaturan.php');
}

$file = $_FILES['sql_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION));
if ($ext !== 'sql') {
    set_flash('error', 'File harus berekstensi .sql');
    redirect('pages/pengaturan.php');
}

$sql = file_get_contents($file);
if ($sql === false) {
    set_flash('error', 'Gagal membaca file.');
    redirect('pages/pengaturan.php');
}

// Eksekusi multi-statement
try {
    db()->exec($sql);
    log_aktivitas('Restore', 'Pengaturan', 'Melakukan restore database');
    set_flash('success', 'Database berhasil dipulihkan.');
} catch (Exception $e) {
    set_flash('error', 'Gagal restore: ' . $e->getMessage());
}
redirect('pages/pengaturan.php');
