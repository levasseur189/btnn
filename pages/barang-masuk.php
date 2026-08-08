<?php
$page_title = 'Barang Masuk';
$active_menu = 'masuk';
require_once __DIR__ . '/../includes/header.php';

$rows = db()->query("
    SELECT bm.*, b.nama AS barang_nama, b.kode AS barang_kode, b.satuan, s.nama AS supplier_nama, ad.nama AS admin_nama
    FROM barang_masuk bm
    JOIN barang b ON b.id = bm.barang_id
    LEFT JOIN supplier s ON s.id = bm.supplier_id
    JOIN admin ad ON ad.id = bm.admin_id
    ORDER BY bm.tanggal DESC, bm.id DESC
")->fetchAll();

$barangs = db()->query('SELECT id, kode, nama, satuan FROM barang ORDER BY nama ASC')->fetchAll();
$suppliers = db()->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();
clear_old();
?>

<div class="page-title-bar fade-in">
    <h4>Barang Masuk</h4>
    <nav class="breadcrumb">
        <a class="breadcrumb-item" href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <span class="breadcrumb-item active">Barang Masuk</span>
    </nav>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-box-arrow-in-down text-success me-2"></i>Form Barang Masuk</span></div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/actions/barang-masuk-save.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="required">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barang <span class="required">*</span></label>
                        <select name="barang_id" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($barangs as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['kode']) ?> - <?= e($b['nama']) ?> (<?= e($b['satuan']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah <span class="required">*</span></label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Invoice</label>
                        <input type="text" name="nomor_invoice" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Bukti</label>
                        <input type="file" name="bukti" class="form-control" accept="image/*,application/pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-save me-1"></i> Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><span><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Barang Masuk</span></div>
            <div class="card-body">
                <?php if (empty($rows)): ?>
                    <div class="empty-state"><?= empty_state_svg('box') ?><h5>Belum ada transaksi masuk</h5></div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable w-100">
                        <thead><tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Jumlah</th><th>Supplier</th><th>Invoice</th><th>Admin</th><th class="no-print">Bukti</th></tr></thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><?= format_tgl_singkat($r['tanggal']) ?></td>
                                <td><?= e($r['barang_kode']) ?></td>
                                <td><?= e($r['barang_nama']) ?></td>
                                <td><span class="text-success fw-semibold">+<?= format_angka($r['jumlah']) ?></span> <?= e($r['satuan']) ?></td>
                                <td><?= e($r['supplier_nama'] ?? '-') ?></td>
                                <td><?= e($r['nomor_invoice'] ?? '-') ?></td>
                                <td><?= e($r['admin_nama']) ?></td>
                                <td class="no-print">
                                    <?php if (!empty($r['bukti'])): ?>
                                        <a href="<?= UPLOAD_URL ?>/<?= e($r['bukti']) ?>" target="_blank" class="btn-icon-sm"><i class="bi bi-paperclip"></i></a>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
