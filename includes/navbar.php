<?php
$admin = $admin ?? current_admin();
$foto_src = BASE_URL . '/images/avatar-default.png';
if (!empty($admin['foto']) && file_exists(ROOT_PATH . '/uploads/' . $admin['foto'])) {
    $foto_src = UPLOAD_URL . '/' . $admin['foto'];
}
?>
<nav class="topbar">
    <button class="btn-icon" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>
    <form class="topbar-search" action="<?= BASE_URL ?>/pages/search.php" method="get" autocomplete="off">
        <i class="bi bi-search"></i>
        <input type="text" name="q" placeholder="Cari barang, kode, supplier, kategori... (Alt+S)" value="<?= e($_GET['q'] ?? '') ?>" id="globalSearchInput">
    </form>
    <div class="topbar-right">
        <button class="btn-icon" id="darkModeToggle" aria-label="Toggle dark mode" title="Mode Gelap (Alt+D)">
            <i class="bi bi-moon-stars"></i>
        </button>
        <div class="dropdown">
            <button class="profile-btn" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= e($foto_src) ?>" alt="Avatar" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($admin['nama'] ?? 'Admin') ?>&background=0066B3&color=fff&bold=true'">
                <span class="profile-name d-none d-md-inline"><?= e($admin['nama'] ?? 'Admin') ?></span>
                <i class="bi bi-chevron-down d-none d-md-inline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header">
                    <div class="fw-semibold"><?= e($admin['nama'] ?? '') ?></div>
                    <div class="small text-muted"><?= e($admin['username'] ?? '') ?></div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/pengaturan.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/pages/pengaturan.php#password"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
