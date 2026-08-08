<?php
$page_title = 'Pencarian';
$active_menu = '';
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $results = db()->prepare("
        SELECT b.kode, b.nama, b.satuan, b.stok, b.minimal_stok, k.nama AS kategori, s.nama AS supplier
        FROM barang b
        LEFT JOIN kategori k ON k.id = b.kategori_id
        LEFT JOIN supplier s ON s.id = b.supplier_id
        WHERE b.nama LIKE ? OR b.kode LIKE ? OR k.nama LIKE ? OR s.nama LIKE ?
        ORDER BY b.kode ASC
    ");
    $results->execute([$like, $like, $like, $like]);
    $results = $results->fetchAll();
}
?>

<div class="page-title-bar fade-in">
    <h4>Hasil Pencarian</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Pencarian: "<?= e($q) ?>"</span>
    </nav>
</div>

<div class="card">
    <div class="card-header"><span><i class="bi bi-search text-primary me-2"></i>Hasil untuk "<?= e($q) ?>"</span></div>
    <div class="card-body">
        <?php if ($q === ''): ?>
            <div class="empty-state"><?= empty_state_svg('search') ?><h5>Masukkan kata kunci</h5><p>Gunakan kotak pencarian di navbar.</p></div>
        <?php elseif (empty($results)): ?>
            <div class="empty-state"><?= empty_state_svg('search') ?><h5>Tidak ada hasil</h5><p>Tidak ditemukan barang untuk "<?= e($q) ?>".</p></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover w-100">
                <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Supplier</th><th>Stok</th><th>Status</th><th class="no-print">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($results as $r): $st = status_stok($r['stok'], $r['minimal_stok']); ?>
                    <tr>
                        <td><strong><?= e($r['kode']) ?></strong></td>
                        <td><?= e($r['nama']) ?></td>
                        <td><?= e($r['kategori'] ?? '-') ?></td>
                        <td><?= e($r['supplier'] ?? '-') ?></td>
                        <td><?= format_angka($r['stok']) ?> <?= e($r['satuan']) ?></td>
                        <td><?= status_badge($st) ?></td>
                        <td class="no-print"><a href="<?= BASE_URL ?>/pages/detail-barang.php?kode=<?= urlencode($r['kode']) ?>" class="btn-icon-sm"><i class="bi bi-eye"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
