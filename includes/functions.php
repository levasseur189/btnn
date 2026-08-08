<?php
// Fungsi helper umum aplikasi

require_once __DIR__ . '/../config/database.php';

function e($value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function current_admin(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    $stmt = db()->prepare('SELECT * FROM admin WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function log_aktivitas(string $aksi, string $modul, string $keterangan): void {
    if (empty($_SESSION['admin_id'])) return;
    $stmt = db()->prepare('INSERT INTO aktivitas (admin_id, aksi, modul, keterangan) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_SESSION['admin_id'], $aksi, $modul, $keterangan]);
}

function status_stok(int $stok, int $minimal): string {
    if ($stok <= 0) return 'habis';
    if ($stok <= $minimal) return 'menipis';
    return 'aman';
}

function status_badge(string $status): string {
    return match ($status) {
        'aman' => '<span class="badge bg-success-subtle text-success-emerald"><i class="bi bi-check-circle"></i> Aman</span>',
        'menipis' => '<span class="badge bg-warning-subtle text-warning"><i class="bi bi-exclamation-triangle"></i> Menipis</span>',
        'habis' => '<span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i> Habis</span>',
        default => $status,
    };
}

function empty_state_svg(string $type = 'default'): string {
    $svgs = [
        'default' => '<svg class="empty-state-svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="40" y="30" width="120" height="100" rx="8" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2"/><rect x="55" y="45" width="90" height="8" rx="4" fill="#CBD5E1"/><rect x="55" y="62" width="70" height="8" rx="4" fill="#CBD5E1"/><rect x="55" y="79" width="80" height="8" rx="4" fill="#CBD5E1"/><circle cx="100" cy="110" r="15" fill="#0066B3" opacity="0.15"/><path d="M93 110 L98 115 L107 105" stroke="#0066B3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
        'box' => '<svg class="empty-state-svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M100 35 L140 55 L100 75 L60 55 Z" fill="#E2E8F0" stroke="#94A3B8" stroke-width="2"/><path d="M60 55 L60 95 L100 115 L100 75" fill="#CBD5E1" stroke="#94A3B8" stroke-width="2"/><path d="M140 55 L140 95 L100 115 L100 75" fill="#94A3B8" opacity="0.5" stroke="#94A3B8" stroke-width="2"/><path d="M100 75 L100 115" stroke="#64748B" stroke-width="2"/><circle cx="100" cy="130" r="4" fill="#0066B3" opacity="0.3"/></svg>',
        'search' => '<svg class="empty-state-svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="85" cy="75" r="35" fill="none" stroke="#CBD5E1" stroke-width="3"/><line x1="110" y1="100" x2="140" y2="130" stroke="#94A3B8" stroke-width="4" stroke-linecap="round"/><circle cx="85" cy="75" r="18" fill="#0066B3" opacity="0.1"/><text x="85" y="80" text-anchor="middle" font-size="20" fill="#94A3B8">?</text></svg>',
        'success' => '<svg class="empty-state-svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="80" r="40" fill="#22C55E" opacity="0.1"/><circle cx="100" cy="80" r="30" fill="none" stroke="#22C55E" stroke-width="2.5"/><path d="M88 80 L96 88 L112 72" stroke="#22C55E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>',
        'clock' => '<svg class="empty-state-svg" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="80" r="35" fill="#E2E8F0" stroke="#94A3B8" stroke-width="2.5"/><path d="M100 58 L100 80 L115 90" stroke="#0066B3" stroke-width="3" stroke-linecap="round" fill="none"/><circle cx="100" cy="80" r="3" fill="#0066B3"/></svg>',
    ];
    return $svgs[$type] ?? $svgs['default'];
}

function format_tanggal($date): string {
    if (empty($date)) return '-';
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $bulan[(int)date('m', $ts) - 1] . ' ' . date('Y', $ts);
}

function format_tgl_singkat($date): string {
    if (empty($date)) return '-';
    return date('d/m/Y', strtotime($date));
}

function format_datetime($datetime): string {
    if (empty($datetime)) return '-';
    return date('d/m/Y H:i', strtotime($datetime));
}

function format_angka($n): string {
    return number_format($n, 0, ',', '.');
}

function upload_file(array $file, string $folder, array $allowed_ext, int $max_size = MAX_UPLOAD_SIZE): ?string {
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) return null;
    if ($file['size'] > $max_size) return null;
    $dir = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $filename = uniqid() . '.' . $ext;
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
    return $folder . '/' . $filename;
}

function generate_kode(string $prefix, string $table, string $column): string {
    $stmt = db()->query("SELECT $column FROM $table ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    $num = 1;
    if ($last) {
        $num = (int)ltrim(substr($last[$column], strlen($prefix)), '0') + 1;
    }
    return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
}

function flash(string $key): ?string {
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function set_flash(string $key, string $value): void {
    $_SESSION['_flash'][$key] = $value;
}

function old(string $key, $default = ''): string {
    return e($_SESSION['_old'][$key] ?? $default);
}

function set_old(array $data): void {
    $_SESSION['_old'] = $data;
}

function clear_old(): void {
    unset($_SESSION['_old']);
}

function count_total_barang(): int {
    return (int)db()->query('SELECT COUNT(*) FROM barang')->fetchColumn();
}

function count_hampir_habis(): int {
    $stmt = db()->query('SELECT COUNT(*) FROM barang WHERE stok <= minimal_stok');
    return (int)$stmt->fetchColumn();
}

function count_masuk_hari_ini(): int {
    $stmt = db()->prepare('SELECT COALESCE(SUM(jumlah),0) FROM barang_masuk WHERE tanggal = CURDATE()');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function count_keluar_hari_ini(): int {
    $stmt = db()->prepare('SELECT COALESCE(SUM(jumlah),0) FROM barang_keluar WHERE tanggal = CURDATE()');
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
