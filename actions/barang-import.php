<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file_excel'])) {
    set_flash('error', 'Tidak ada file yang diunggah.');
    redirect('pages/barang.php');
}

$file = $_FILES['file_excel'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    set_flash('error', 'Gagal mengunggah file. Silakan coba lagi.');
    redirect('pages/barang.php');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['xlsx', 'xls'])) {
    set_flash('error', 'Format file tidak didukung. Gunakan file .xlsx atau .xls');
    redirect('pages/barang.php');
}

try {
    $spreadsheet = IOFactory::load($file['tmp_name']);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, false);
} catch (Throwable $e) {
    set_flash('error', 'Gagal membaca file Excel: ' . $e->getMessage());
    redirect('pages/barang.php');
}

if (count($rows) < 2) {
    set_flash('error', 'File Excel kosong atau tidak memiliki data.');
    redirect('pages/barang.php');
}

// Preload kategori and supplier maps for name->id lookup
$kategori_map = [];
foreach (db()->query('SELECT id, nama, kode FROM kategori')->fetchAll() as $k) {
    $kategori_map[mb_strtolower(trim($k['nama']))] = (int)$k['id'];
    $kategori_map[mb_strtolower(trim($k['kode']))] = (int)$k['id'];
}
$supplier_map = [];
foreach (db()->query('SELECT id, nama FROM supplier')->fetchAll() as $s) {
    $supplier_map[mb_strtolower(trim($s['nama']))] = (int)$s['id'];
}

// Load existing kode to skip duplicates
$existing_kode = [];
foreach (db()->query('SELECT kode FROM barang')->fetchAll() as $b) {
    $existing_kode[mb_strtolower(trim($b['kode']))] = true;
}

// Detect header row: if row[0] contains "kode" (case-insensitive), skip it
$startRow = 0;
$headerRow = $rows[0];
if (is_string($headerRow[0]) && mb_strtolower(trim($headerRow[0])) === 'kode') {
    $startRow = 1;
}

$imported = 0;
$skipped = 0;
$errors = [];
$insertSql = 'INSERT INTO barang (kode, nama, kategori_id, supplier_id, lokasi_rak, satuan, stok, minimal_stok, qr_code, deskripsi) VALUES (?,?,?,?,?,?,?,?,?,?)';
$stmt = db()->prepare($insertSql);

for ($i = $startRow; $i < count($rows); $i++) {
    $r = $rows[$i];
    $kode = trim((string)($r[0] ?? ''));
    $nama = trim((string)($r[1] ?? ''));
    $kategori_nama = trim((string)($r[2] ?? ''));
    $supplier_nama = trim((string)($r[3] ?? ''));
    $lokasi_rak = trim((string)($r[4] ?? ''));
    $satuan = trim((string)($r[5] ?? ''));
    $stok = (int)($r[6] ?? 0);
    $minimal_stok = (int)($r[7] ?? 5);
    $deskripsi = trim((string)($r[8] ?? ''));

    if ($kode === '' && $nama === '') {
        continue;
    }

    $line = $i + 1;
    if ($kode === '') { $errors[] = "Baris $line: Kode kosong, dilewati."; $skipped++; continue; }
    if ($nama === '') { $errors[] = "Baris $line: Nama kosong, dilewati."; $skipped++; continue; }

    $kode_lower = mb_strtolower($kode);
    if (isset($existing_kode[$kode_lower])) {
        $errors[] = "Baris $line: Kode '$kode' sudah ada, dilewati.";
        $skipped++;
        continue;
    }

    $kategori_id = $kategori_map[mb_strtolower($kategori_nama)] ?? 0;
    if ($kategori_id === 0) {
        $errors[] = "Baris $line: Kategori '$kategori_nama' tidak ditemukan, dilewati.";
        $skipped++;
        continue;
    }

    $supplier_id = null;
    if ($supplier_nama !== '' && $supplier_nama !== '-') {
        $supplier_id = $supplier_map[mb_strtolower($supplier_nama)] ?? null;
        if ($supplier_id === null) {
            $errors[] = "Baris $line: Supplier '$supplier_nama' tidak ditemukan, dilewati.";
            $skipped++;
            continue;
        }
    }

    if ($satuan === '') $satuan = 'pcs';

    try {
        $stmt->execute([$kode, $nama, $kategori_id, $supplier_id, $lokasi_rak ?: null, $satuan, $stok, $minimal_stok, $kode, $deskripsi ?: null]);
        $existing_kode[$kode_lower] = true;
        $imported++;
    } catch (Throwable $e) {
        $errors[] = "Baris $line: Gagal menyimpan - " . $e->getMessage();
        $skipped++;
    }
}

log_aktivitas('Import', 'Barang', "Import Excel: $imported barang masuk, $skipped dilewati");

$msg = "Import selesai: $imported barang berhasil ditambahkan.";
if ($skipped > 0) $msg .= " $skipped baris dilewati.";
if ($errors) {
    $_SESSION['_flash']['error'] = implode(' ', array_slice($errors, 0, 10));
}
set_flash('success', $msg);
redirect('pages/barang.php');
