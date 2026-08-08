<?php
$menus = [
    'dashboard' => ['Dashboard', 'bi-grid-1x2', 'dashboard.php'],
    'barang' => ['Data Barang', 'bi-box-seam', 'barang.php'],
    'kategori' => ['Kategori', 'bi-tags', 'kategori.php'],
    'supplier' => ['Supplier', 'bi-truck', 'supplier.php'],
    'masuk' => ['Barang Masuk', 'bi-box-arrow-in-down', 'barang-masuk.php'],
    'keluar' => ['Barang Keluar', 'bi-box-arrow-right', 'barang-keluar.php'],
    'laporan' => ['Laporan', 'bi-bar-chart-line', 'laporan.php'],
    'pengaturan' => ['Pengaturan', 'bi-gear', 'pengaturan.php'],
];
$collapsed = !empty($_COOKIE['sidebar_collapsed']);
?>
<aside class="sidebar <?= $collapsed ? 'collapsed' : '' ?>" id="sidebar">
    <div class="sidebar-brand">
        <img src="<?= BASE_URL ?>/images/logo-btn.png" alt="BTN" class="brand-logo" onerror="this.style.display='none'">
        <div class="brand-text">
            <span class="brand-title">IMS BTN</span>
            <span class="brand-sub">Inventory System</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menus as $key => [$label, $icon, $file]): ?>
            <a href="<?= BASE_URL ?>/pages/<?= $file ?>" class="nav-item <?= $active_menu === $key ? 'active' : '' ?>">
                <i class="bi <?= $icon ?>"></i>
                <span class="nav-label"><?= $label ?></span>
            </a>
        <?php endforeach; ?>
        <a href="<?= BASE_URL ?>/logout.php" class="nav-item text-danger">
            <i class="bi bi-box-arrow-left"></i>
            <span class="nav-label">Logout</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="small text-muted">v<?= APP_VERSION ?></div>
    </div>
</aside>
