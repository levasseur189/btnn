<?php
$page_title = 'Detail Barang';
$active_menu = 'barang';
require_once __DIR__ . '/../includes/header.php';

$kode = $_GET['kode'] ?? '';
$stmt = db()->prepare("
    SELECT b.*, k.nama AS kategori_nama, s.nama AS supplier_nama
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN supplier s ON s.id = b.supplier_id
    WHERE b.kode = ?
");
$stmt->execute([$kode]);
$barang = $stmt->fetch();
if (!$barang) { set_flash('error', 'Barang tidak ditemukan.'); redirect('pages/barang.php'); }

$masuk = db()->prepare("
    SELECT bm.*, s.nama AS supplier_nama, ad.nama AS admin_nama
    FROM barang_masuk bm
    LEFT JOIN supplier s ON s.id = bm.supplier_id
    JOIN admin ad ON ad.id = bm.admin_id
    WHERE bm.barang_id = ? ORDER BY bm.tanggal DESC, bm.id DESC
");
$masuk->execute([$barang['id']]);
$masuk = $masuk->fetchAll();

$keluar = db()->prepare("
    SELECT bk.*, ad.nama AS admin_nama
    FROM barang_keluar bk
    JOIN admin ad ON ad.id = bk.admin_id
    WHERE bk.barang_id = ? ORDER BY bk.tanggal DESC, bk.id DESC
");
$keluar->execute([$barang['id']]);
$keluar = $keluar->fetchAll();

$st = status_stok($barang['stok'], $barang['minimal_stok']);
?>

<div class="page-title-bar fade-in">
    <h4>Detail Barang</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/barang.php">Data Barang</a>
        <span class="breadcrumb-item active"><?= e($barang['nama']) ?></span>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($barang['foto'])): ?>
                    <img src="<?= UPLOAD_URL ?>/<?= e($barang['foto']) ?>" class="img-fluid rounded mb-3" style="max-height:240px;object-fit:contain">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light rounded mb-3" style="height:240px"><i class="bi bi-image fs-1 text-muted"></i></div>
                <?php endif; ?>
                <p class="text-muted small mt-2">Kode: <strong><?= e($barang['kode']) ?></strong></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-info-circle text-primary me-2"></i>Informasi Barang</span>
                <div class="no-print">
                    <a href="<?= BASE_URL ?>/pages/barang-form.php?id=<?= $barang['id'] ?>" class="btn btn-sm btn-soft"><i class="bi bi-pencil me-1"></i> Edit</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small">Kode Barang</label><div class="fw-semibold"><?= e($barang['kode']) ?></div></div>
                    <div class="col-md-6"><label class="text-muted small">Nama Barang</label><div class="fw-semibold"><?= e($barang['nama']) ?></div></div>
                    <div class="col-md-6"><label class="text-muted small">Kategori</label><div><?= e($barang['kategori_nama']) ?></div></div>
                    <div class="col-md-6"><label class="text-muted small">Supplier</label><div><?= e($barang['supplier_nama'] ?? '-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Lokasi Rak</label><div><?= e($barang['lokasi_rak'] ?? '-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Satuan</label><div><?= e($barang['satuan']) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Status</label><div><?= status_badge($st) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Stok Saat Ini</label><div class="fw-bold fs-5 text-primary"><?= format_angka($barang['stok']) ?> <?= e($barang['satuan']) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Minimal Stok</label><div><?= format_angka($barang['minimal_stok']) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Tanggal Dibuat</label><div><?= format_tanggal($barang['created_at']) ?></div></div>
                    <div class="col-12"><label class="text-muted small">Deskripsi</label><div><?= e($barang['deskripsi'] ?? '-') ?></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-box-arrow-in-down text-success me-2"></i>Riwayat Barang Masuk</span></div>
            <div class="card-body">
                <?php if (empty($masuk)): ?>
                    <div class="empty-state"><i class="bi bi-inbox"></i><h5>Belum ada riwayat masuk</h5></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Invoice</th><th>Admin</th></tr></thead>
                        <tbody>
                        <?php foreach ($masuk as $m): ?>
                            <tr><td><?= format_tgl_singkat($m['tanggal']) ?></td><td>+<?= format_angka($m['jumlah']) ?></td><td><?= e($m['nomor_invoice'] ?? '-') ?></td><td><?= e($m['admin_nama']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-box-arrow-right text-danger me-2"></i>Riwayat Barang Keluar</span></div>
            <div class="card-body">
                <?php if (empty($keluar)): ?>
                    <div class="empty-state"><i class="bi bi-inbox"></i><h5>Belum ada riwayat keluar</h5></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Tanggal</th><th>Jumlah</th><th>Tujuan</th><th>Admin</th></tr></thead>
                        <tbody>
                        <?php foreach ($keluar as $k): ?>
                            <tr><td><?= format_tgl_singkat($k['tanggal']) ?></td><td>-<?= format_angka($k['jumlah']) ?></td><td><?= e($k['tujuan'] ?? '-') ?></td><td><?= e($k['admin_nama']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
