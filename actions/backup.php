<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Backup sederhana: export semua tabel ke SQL INSERT
$tables = ['admin', 'kategori', 'supplier', 'barang', 'barang_masuk', 'barang_keluar', 'aktivitas'];
$out = "-- IMS BTN Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $tbl) {
    $rows = db()->query("SELECT * FROM $tbl")->fetchAll();
    $out .= "-- Table: $tbl\n";
    $out .= "TRUNCATE TABLE `$tbl`;\n";
    foreach ($rows as $row) {
        $cols = array_keys($row);
        $vals = array_map(function ($v) {
            return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
        }, array_values($row));
        $out .= "INSERT INTO `$tbl` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n";
    }
    $out .= "\n";
}

log_aktivitas('Backup', 'Pengaturan', 'Melakukan backup database');
header('Content-Type: application/sql');
header('Content-Disposition: attachment;filename="ims-btn-backup-' . date('Ymd-His') . '.sql"');
echo $out;
