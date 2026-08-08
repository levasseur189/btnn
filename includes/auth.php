<?php
// Autentikasi & proteksi halaman

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

function is_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
    check_timeout();
    $_SESSION['last_activity'] = time();
}

function check_timeout(): void {
    if (!empty($_SESSION['last_activity'])) {
        $idle = time() - $_SESSION['last_activity'];
        if ($idle > SESSION_TIMEOUT) {
            do_logout();
            set_flash('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            redirect('login.php');
        }
    }
}

function do_login(string $username, string $password): bool {
    $stmt = db()->prepare('SELECT * FROM admin WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_nama'] = $admin['nama'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['last_activity'] = time();
    log_aktivitas('Login', 'Auth', 'Admin ' . $admin['nama'] . ' login');
    return true;
}

function do_logout(): void {
    if (!empty($_SESSION['admin_id'])) {
        log_aktivitas('Logout', 'Auth', 'Admin ' . ($_SESSION['admin_nama'] ?? '') . ' logout');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
