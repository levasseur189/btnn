<?php
/**
 * Sistem Database Migration untuk IMS BTN
 * Menjalankan migration SQL secara berurutan berdasarkan nomor versi.
 * Tabel _migrations mencatat migration yang sudah dijalankan.
 *
 * Cara pakai (CLI): php database/migrate.php
 * Atau akses via browser: http://localhost/ims-btn/database/migrate.php
 */

require_once __DIR__ . '/../config/database.php';

function migrations_dir(): string {
    return __DIR__ . '/migrations';
}

function ensure_migrations_table(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS _migrations (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        filename VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function get_applied_migrations(): array {
    ensure_migrations_table();
    $stmt = db()->query('SELECT filename FROM _migrations ORDER BY filename ASC');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function run_migrations(): array {
    $dir = migrations_dir();
    $log = [];
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $applied = get_applied_migrations();
    $files = glob($dir . '/*.sql');
    sort($files);
    if (empty($files)) {
        $log[] = 'Tidak ada file migration ditemukan di database/migrations/';
        return $log;
    }
    $new_count = 0;
    foreach ($files as $file) {
        $filename = basename($file);
        if (in_array($filename, $applied)) {
            continue;
        }
        $sql = file_get_contents($file);
        if ($sql === false) continue;
        try {
            db()->exec($sql);
            $stmt = db()->prepare('INSERT INTO _migrations (filename) VALUES (?)');
            $stmt->execute([$filename]);
            $log[] = "[OK] Migration '$filename' berhasil dijalankan.";
            $new_count++;
        } catch (Exception $e) {
            $log[] = "[ERROR] Migration '$filename' gagal: " . $e->getMessage();
            break;
        }
    }
    if ($new_count === 0) {
        $log[] = 'Semua migration sudah terbaru. Tidak ada perubahan.';
    }
    return $log;
}

// Jalankan jika diakses langsung
if (php_sapi_name() === 'cli' || (isset($_GET['run']) && $_GET['run'] === '1')) {
    echo "=== IMS BTN Database Migration ===\n";
    $results = run_migrations();
    foreach ($results as $line) {
        echo $line . "\n";
    }
    echo "=== Selesai ===\n";
}
