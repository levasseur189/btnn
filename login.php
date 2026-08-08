<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('pages/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (!do_login($username, $password)) {
        $error = 'Username atau password salah.';
    } else {
        redirect('pages/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-illustration">
        <div class="login-illustration-content fade-in">
            <i class="bi bi-building-fill-gear warehouse-icon"></i>
            <h1>Sistem Manajemen Inventaris</h1>
            <p>Kelola stok barang gudang Bank BTN dengan cepat, akurat, dan terlacak. Dirancang khusus untuk pegawai gudang.</p>
        </div>
    </div>
    <div class="login-form-side">
        <div class="login-card fade-in">
            <img src="<?= BASE_URL ?>/images/logo-btn.png" alt="BTN" class="login-logo" onerror="this.style.display='none'">
            <h2 class="login-title">Inventory Management System</h2>
            <p class="login-subtitle">Bank BTN - Silakan masuk untuk melanjutkan</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" autofocus required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required id="pwdInput">
                        <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </form>
            <p class="text-center text-muted small mt-4 mb-0">
                <i class="bi bi-shield-lock"></i> Sistem ini hanya untuk Admin Gudang Bank BTN
            </p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('togglePwd').addEventListener('click', function(){
    var i = document.getElementById('pwdInput');
    var icon = this.querySelector('i');
    if (i.type === 'password') { i.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { i.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>
</body>
</html>
